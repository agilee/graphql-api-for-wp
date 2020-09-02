<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\ModuleResolvers;

use Parsedown;

trait HasMarkdownDocumentationModuleResolverTrait
{
    /**
     * The module slug
     */
    abstract public function getSlug(string $module): string;

    /**
     * The name of the Markdown filename.
     * By default, it's the same as the slug
     *
     * @param string $module
     * @return string
     */
    public function getMarkdownFilename(string $module): ?string
    {
        return $this->getSlug($module) . '.md';
    }

    /**
     * Where the markdown file localized to the user's language is stored
     *
     * @param string $module
     * @return string
     */
    abstract public function getLocalizedMarkdownFileDir(string $module): string;

    /**
     * Where the default markdown file (for if the localized language is not available) is stored
     *
     * @param string $module
     * @return string
     */
    abstract public function getDefaultMarkdownFileDir(string $module): string;

    /**
     * Does the module have HTML Documentation?
     *
     * @param string $module
     * @return bool
     */
    public function hasDocumentation(string $module): bool
    {
        return !empty($this->getMarkdownFilename($module));
    }

    /**
     * Path URL to append to the local images referenced in the markdown file
     *
     * @param string $module
     * @return string|null
     */
    abstract protected function getDefaultMarkdownFileURL(string $module): string;

    /**
     * HTML Documentation for the module
     *
     * @param string $module
     * @return string|null
     */
    public function getDocumentation(string $module): ?string
    {
        if ($markdownFilename = $this->getMarkdownFilename($module)) {
            $localizedMarkdownFile = \trailingslashit($this->getLocalizedMarkdownFileDir($module)) . $markdownFilename;
            if (file_exists($localizedMarkdownFile)) {
                // First check if the localized version exists
                $markdownFile = $localizedMarkdownFile;
            } else {
                // Otherwise, use the default language version
                $markdownFile = \trailingslashit($this->getDefaultMarkdownFileDir($module)) . $markdownFilename;
                // Make sure this file exists
                if (!file_exists($markdownFile)) {
                    return sprintf(
                        '<p>%s</p>',
                        \__('Oops, the documentation for this module is not available', 'graphql-api')
                    );
                }
            }
            $markdownContents = file_get_contents($markdownFile);
            $htmlContent = (new Parsedown())->text($markdownContents);
            return $this->processHTMLContent($module, $htmlContent);
        }
        return null;
    }

    /**
     * Process the HTML content:
     *
     * - Add the path to the images and anchors
     * - Add classes to HTML elements
     * - Append video embeds
     */
    protected function processHTMLContent(string $module, string $htmlContent): string
    {
        $defaultModulePathURL = $this->getDefaultMarkdownFileURL($module);
        // Add the path to the images and anchors
        $htmlContent = $this->appendPathURLToImages($defaultModulePathURL, $htmlContent);
        $htmlContent = $this->appendPathURLToAnchors($defaultModulePathURL, $htmlContent);
        // Add classes to HTML elements
        $htmlContent = $this->addClasses($htmlContent);
        // Append video embeds
        $htmlContent = $this->embedVideos($htmlContent);
        // Convert the <h2> into tabs
        $htmlContent = $this->tabContent($htmlContent);
        return $htmlContent;
    }

    /**
     * Add tabs to the content wherever there is an <h2>
     */
    protected function tabContent(string $htmlContent): string
    {
        $tag = 'h2';
        $firstTagPos = strpos($htmlContent, '<' . $tag . '>');
        // Check if there is any <h2>
        if ($firstTagPos !== false) {
            // Content before the first <h2> does not go within any tab
            $contentStarter = substr(
                $htmlContent,
                0,
                $firstTagPos
            );
            // Add the markup for the tabs around every <h2>
            $regex = sprintf(
                '/<%1$s>(.*?)<\/%1$s>/',
                $tag
            );
            $headers = [];
            $panelContent = preg_replace_callback(
                $regex,
                function ($matches) use (&$headers) {
                    $isFirstTab = empty($headers);
                    if (!$isFirstTab) {
                        $tabbedPanel = '</div>';
                    } else {
                        $tabbedPanel = '';
                    }
                    $headers[] = $matches[1];
                    return $tabbedPanel . sprintf(
                        '<div id="doc-panel-%s" class="tab-content" style="display: %s;">',
                        count($headers),
                        $isFirstTab ? 'block' : 'none'
                    );// . $matches[0];
                },
                substr(
                    $htmlContent,
                    $firstTagPos
                )
            ) . '</div>';

            // Create the tabs
            $panelTabs = '<h2 class="nav-tab-wrapper">';
            $headersCount = count($headers);
            for ($i = 0; $i < $headersCount; $i++) {
                $isFirstTab = $i == 0;
                $panelTabs .= sprintf(
                    '<a href="#doc-panel-%s" class="nav-tab %s">%s</a>',
                    $i + 1,
                    $isFirstTab ? 'nav-tab-active' : '',
                    $headers[$i]
                );
            }
            $panelTabs .= '</h2>';

            return
                $contentStarter
                . '<div class="graphql-api-tabpanel">'
                . $panelTabs
                . $panelContent
                . '</div>';
        }
        return $htmlContent;
    }

    /**
     * Append video embeds. These are not already in the markdown file
     * because GitHub can't add `<iframe>`. Then, the source only contains
     * a link to the video. This must be identified, and transformed into
     * the embed.
     *
     * The match is produced when a link is pointing to a video in
     * Vimeo or Youtube by the end of the paragraph, with/out a final dot.
     */
    protected function embedVideos(string $htmlContent): string
    {
        // Identify videos from Vimeo/Youtube
        return (string)preg_replace_callback(
            '/<p>(.*?)<a href="https:\/\/(vimeo.com|youtube.com|youtu.be)\/(.*?)">(.*?)<\/a>\.?<\/p>/',
            function ($matches) {
                global $wp_embed;
                // Keep the link, and append the embed immediately after
                return
                    $matches[0]
                    . '<div class="video-responsive-container">' .
                        $wp_embed->autoembed(sprintf(
                            'https://%s/%s',
                            $matches[2],
                            $matches[3]
                        ))
                    . '</div>';
            },
            $htmlContent
        );
    }

    /**
     * Add classes to the HTML elements
     */
    protected function addClasses(string $htmlContent): string
    {
        /**
         * Add class "wp-list-table widefat" to all tables
         */
        return str_replace(
            '<table>',
            '<table class="wp-list-table widefat striped">',
            $htmlContent
        );
    }

    /**
     * Convert relative paths to absolute paths for image URLs
     *
     * @param string $pathURL
     * @param string $htmlContent
     * @return string
     */
    protected function appendPathURLToImages(string $pathURL, string $htmlContent): string
    {
        return $this->appendPathURLToElement('img', 'src', $pathURL, $htmlContent);
    }

    /**
     * Convert relative paths to absolute paths for image URLs
     *
     * @param string $pathURL
     * @param string $htmlContent
     * @return string
     */
    protected function appendPathURLToAnchors(string $pathURL, string $htmlContent): string
    {
        return $this->appendPathURLToElement('a', 'href', $pathURL, $htmlContent);
    }

    /**
     * Convert relative paths to absolute paths for elements
     *
     * @param string $tag
     * @param string $attr
     * @param string $pathURL
     * @param string $htmlContent
     * @return string
     */
    protected function appendPathURLToElement(string $tag, string $attr, string $pathURL, string $htmlContent): string
    {
        /**
         * $regex will become:
         * - /<img.*src="(.*?)".*?>/
         * - /<a.*href="(.*?)".*?>/
         */
        $regex = sprintf(
            '/<%s.*%s="(.*?)".*?>/',
            $tag,
            $attr
        );
        return (string)preg_replace_callback(
            $regex,
            function ($matches) use ($pathURL, $attr) {
                // If the element has an absolute route, then no need
                if (substr($matches[1], 0, strlen('http://')) == 'http://'
                    || substr($matches[1], 0, strlen('https://')) == 'https://'
                ) {
                    return $matches[0];
                }
                $elementURL = \trailingslashit($pathURL) . $matches[1];
                return str_replace(
                    "{$attr}=\"{$matches[1]}\"",
                    "{$attr}=\"{$elementURL}\"",
                    $matches[0]
                );
            },
            $htmlContent
        );
    }
}
