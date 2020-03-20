<?php
namespace Leoloso\GraphQLByPoPWPPlugin\QueryExecution;

use Leoloso\GraphQLByPoPWPPlugin\PluginState;
use PoP\CacheControl\Facades\CacheControlManagerFacade;

class CacheControlGraphQLQueryConfigurator extends AbstractGraphQLQueryConfigurator
{
    protected function doInit(): void
    {
        $this->setCacheControlList();
    }

    /**
     * Extract the access control items defined in the CPT, and inject them into the service as to take effect in the current GraphQL query
     *
     * @return void
     */
    protected function setCacheControlList()
    {
        // If we found a CCL, load its rules/restrictions
        if ($cclPostID = $this->getConfigurationCustomPostID('ccl-post-id')) {
            $cclPost = \get_post($cclPostID);
            $blocks = \parse_blocks($cclPost->post_content);
            // Obtain the blocks of "Access Control" type
            $cclBlock = PluginState::getCacheControlBlock();
            $cclBlockFullName = $cclBlock->getBlockFullName();
            $cclBlockItems = array_filter(
                $blocks,
                function($block) use($cclBlockFullName) {
                    return $block['blockName'] == $cclBlockFullName;
                }
            );
            $cacheControlManager = CacheControlManagerFacade::getInstance();
            // The "Cache Control" type contains the fields/directives and the max-age
            foreach ($cclBlockItems as $cclBlockItem) {
                $maxAge = $cclBlockItem['attrs']['cacheControlMaxAge'];
                if (!is_null($maxAge) && $maxAge >= 0) {
                    // Extract the saved fields
                    if ($typeFields = $cclBlockItem['attrs']['typeFields']) {
                        if ($entriesForFields = array_filter(
                            array_map(
                                function($selectedField) use($maxAge) {
                                    return $this->getEntryFromField($selectedField, $maxAge);
                                },
                                $typeFields
                            )
                        )) {
                            $cacheControlManager->addEntriesForFields(
                                $entriesForFields
                            );
                        }
                    }

                    // Extract the saved directives
                    if ($directives = $cclBlockItem['attrs']['directives']) {
                        if ($entriesForDirectives = array_filter(
                            array_map(
                                function($selectedDirective) use($maxAge) {
                                    return $this->getEntryFromDirective($selectedDirective, $maxAge);
                                },
                                $directives
                            )
                        )) {
                            $cacheControlManager->addEntriesForDirectives(
                                $entriesForDirectives
                            );
                        }
                    }
				}
            }
        }
    }
}
