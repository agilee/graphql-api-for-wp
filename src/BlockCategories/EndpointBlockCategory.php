<?php

declare(strict_types=1);

namespace Leoloso\GraphQLByPoPWPPlugin\BlockCategories;

use Leoloso\GraphQLByPoPWPPlugin\PostTypes\GraphQLEndpointPostType;

class EndpointBlockCategory extends AbstractBlockCategory
{
    public const ENDPOINT_BLOCK_CATEGORY = 'graphql-api-endpoint';

    /**
     * Custom Post Type for which to enable the block category
     *
     * @return string
     */
    protected function getPostTypes(): array
    {
        return [
            GraphQLEndpointPostType::POST_TYPE,
        ];
    }

    /**
     * Block category's slug
     *
     * @return string
     */
    protected function getBlockCategorySlug(): string
    {
        return self::ENDPOINT_BLOCK_CATEGORY;
    }

    /**
     * Block category's title
     *
     * @return string
     */
    protected function getBlockCategoryTitle(): string
    {
        return __('GraphQL endpoint', 'graphql-api');
    }
}