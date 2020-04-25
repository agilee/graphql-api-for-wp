<?php

declare(strict_types=1);

namespace Leoloso\GraphQLByPoPWPPlugin\BlockCategories;

use Leoloso\GraphQLByPoPWPPlugin\PostTypes\GraphQLFieldDeprecationListPostType;

class FieldDeprecationBlockCategory extends AbstractBlockCategory
{
    public const FIELD_DEPRECATION_BLOCK_CATEGORY = 'graphql-api-field-deprecation';

    /**
     * Custom Post Type for which to enable the block category
     *
     * @return string
     */
    protected function getPostType(): string
    {
        return GraphQLFieldDeprecationListPostType::POST_TYPE;
    }

    /**
     * Block category's slug
     *
     * @return string
     */
    protected function getBlockCategorySlug(): string
    {
        return self::FIELD_DEPRECATION_BLOCK_CATEGORY;
    }

    /**
     * Block category's title
     *
     * @return string
     */
    protected function getBlockCategoryTitle(): string
    {
        return __('Field Deprecations for the GraphQL schema', 'graphql-api');
    }
}