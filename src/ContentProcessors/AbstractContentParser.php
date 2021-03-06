<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\ContentProcessors;

use InvalidArgumentException;
use PoP\ComponentModel\Misc\RequestUtils;
use GraphQLAPI\GraphQLAPI\General\LocaleUtils;
use GraphQLAPI\GraphQLAPI\General\RequestParams;
use GraphQLAPI\GraphQLAPI\PluginConstants;

abstract class AbstractContentParser implements ContentParserInterface
{
    public const PATH_URL_TO_DOCS = 'pathURLToDocs';

    /**
     * Parse the file's Markdown into HTML Content
     *
     * @param string $relativePathDir Dir relative to the /docs/${lang}/ folder
     * @throws InvalidArgumentException When the file is not found
     */
    public function getContent(
        string $filename,
        string $relativePathDir = '',
        array $options = []
    ): string {
        // Make sure the relative path ends with "/"
        if ($relativePathDir) {
            $relativePathDir = \trailingslashit($relativePathDir);
        }
        $localizeFile = \trailingslashit($this->getLocalizedFileDir()) . $filename;
        if (file_exists($localizeFile)) {
            // First check if the localized version exists
            $file = $localizeFile;
        } else {
            // Otherwise, use the default language version
            $file = \trailingslashit($this->getDefaultFileDir()) . $filename;
            // Make sure this file exists
            if (!file_exists($file)) {
                throw new InvalidArgumentException(sprintf(
                    \__('File \'%s\' does not exist', 'graphql-api'),
                    $file
                ));
            }
        }
        $fileContent = file_get_contents($file);
        if ($fileContent === false) {
            throw new InvalidArgumentException(sprintf(
                \__('File \'%s\' is corrupted', 'graphql-api'),
                $file
            ));
        }
        $htmlContent = $this->getHTMLContent($fileContent);
        $pathURL = \trailingslashit($this->getDefaultFileURL()) . $relativePathDir;
        // Include the images from the GitHub repo
        $options[self::PATH_URL_TO_DOCS] = PluginConstants::GITHUB_REPO_DOCS_PATH_URL . $relativePathDir;
        return $this->processHTMLContent($htmlContent, $pathURL, $options);
    }

    /**
     * Where the markdown file localized to the user's language is stored
     */
    public function getLocalizedFileDir(): string
    {
        return $this->getFileDir(LocaleUtils::getLocaleLanguage());
    }

    /**
     * Where the default markdown file (for if the localized language is not available) is stored
     * Default language for documentation: English
     */
    public function getDefaultFileDir(): string
    {
        return $this->getFileDir($this->getDefaultDocsLanguage());
    }

    /**
     * Default language for documentation: English
     */
    public function getDefaultDocsLanguage(): string
    {
        return 'en';
    }

    /**
     * Path where to find the local images
     */
    protected function getFileDir(string $lang): string
    {
        return constant('GRAPHQL_API_DIR') . "/docs/${lang}";
    }

    /**
     * Path URL to append to the local images referenced in the markdown file
     */
    protected function getDefaultFileURL(): string
    {
        $lang = $this->getDefaultDocsLanguage();
        return constant('GRAPHQL_API_URL') . "docs/${lang}";
    }

    /**
     * Process the HTML content:
     *
     * - Add the path to the images and anchors
     * - Add classes to HTML elements
     * - Append video embeds
     */
    abstract protected function getHTMLContent(string $fileContent): string;

    /**
     * Process the HTML content:
     *
     * - Add the path to the images and anchors
     * - Add classes to HTML elements
     * - Append video embeds
     *
     * @param array<string, mixed> $options
     */
    protected function processHTMLContent(string $htmlContent, string $pathURL, array $options = []): string
    {
        // Add default values for the options
        $options = array_merge(
            [
                ContentParserOptions::APPEND_PATH_URL_TO_IMAGES => true,
                ContentParserOptions::APPEND_PATH_URL_TO_ANCHORS => true,
                ContentParserOptions::SUPPORT_MARKDOWN_LINKS => true,
                ContentParserOptions::ADD_CLASSES => true,
                ContentParserOptions::EMBED_VIDEOS => true,
                ContentParserOptions::TAB_CONTENT => false,
            ],
            $options
        );
        // Add the path to the images
        if ($options[ContentParserOptions::APPEND_PATH_URL_TO_IMAGES]) {
            // Enable to override the path for images, to read them from
            // the GitHub repo and avoid including them in the plugin
            $imagePathURL = $options[self::PATH_URL_TO_DOCS] ?? $pathURL;
            $htmlContent = $this->appendPathURLToImages($imagePathURL, $htmlContent);
        }
        // Convert Markdown links: execute before appending path to anchors
        if ($options[ContentParserOptions::SUPPORT_MARKDOWN_LINKS]) {
            $htmlContent = $this->convertMarkdownLinks($htmlContent);
        }
        // Add the path to the anchors
        if ($options[ContentParserOptions::APPEND_PATH_URL_TO_ANCHORS]) {
            $htmlContent = $this->appendPathURLToAnchors($pathURL, $htmlContent);
        }
        // Add classes to HTML elements
        if ($options[ContentParserOptions::ADD_CLASSES]) {
            $htmlContent = $this->addClasses($htmlContent);
        }
        // Append video embeds
        if ($options[ContentParserOptions::EMBED_VIDEOS]) {
            $htmlContent = $this->embedVideos($htmlContent);
        }
        // Convert the <h2> into tabs
        if ($options[ContentParserOptions::TAB_CONTENT]) {
            $htmlContent = $this->tabContent($htmlContent);
        }
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
     * Is the anchor pointing to an URL?
     */
    protected function isAbsoluteURL(string $href): bool
    {
        return \str_starts_with($href, 'http://') || \str_starts_with($href, 'https://');
    }

    /**
     * Whenever a link points to a .md file, convert it
     * so it works also within the plugin
     */
    protected function convertMarkdownLinks(string $htmlContent): string
    {
        return (string)preg_replace_callback(
            '/<a.*href="(.*?)\.md".*?>/',
            function (array $matches): string {
                // If the element has an absolute route, then no need
                if ($this->isAbsoluteURL($matches[1])) {
                    return $matches[0];
                }
                // The URL is the current one, plus attr to open the .md file
                // in a modal window
                $elementURL = \add_query_arg(
                    [
                        RequestParams::TAB => RequestParams::TAB_DOCS,
                        RequestParams::DOC => $matches[1],
                        'TB_iframe' => 'true',
                    ],
                    RequestUtils::getRequestedFullURL()
                );
                /** @var string */
                $link = str_replace(
                    "href=\"{$matches[1]}.md\"",
                    "href=\"{$elementURL}\"",
                    $matches[0]
                );
                // Must also add some classnames
                $classnames = 'thickbox open-plugin-details-modal';
                // 1. If there are classes already
                /** @var string */
                $replacedLink = preg_replace_callback(
                    '/ class="(.*?)"/',
                    function (array $matches) use ($classnames): string {
                        return str_replace(
                            " class=\"{$matches[1]}\"",
                            " class=\"{$matches[1]} {$classnames}\"",
                            $matches[0]
                        );
                    },
                    $link
                );
                // 2. If there were no classes
                if ($replacedLink == $link) {
                    $replacedLink = str_replace(
                        "<a ",
                        "<a class=\"{$classnames}\" ",
                        $link
                    );
                }
                return $replacedLink;
            },
            $htmlContent
        );
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
                if ($this->isAbsoluteURL($matches[1])) {
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
