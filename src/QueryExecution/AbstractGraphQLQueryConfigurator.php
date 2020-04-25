<?php

declare(strict_types=1);

namespace Leoloso\GraphQLByPoPWPPlugin\QueryExecution;

use Leoloso\GraphQLByPoPWPPlugin\Blocks\AbstractBlock;
use Leoloso\GraphQLByPoPWPPlugin\Blocks\BlockConstants;
use PoP\ComponentModel\Facades\Registries\TypeRegistryFacade;
use PoP\ComponentModel\Facades\Instances\InstanceManagerFacade;
use Leoloso\GraphQLByPoPWPPlugin\PostTypes\GraphQLQueryPostType;
use PoP\ComponentModel\Facades\Registries\DirectiveRegistryFacade;
use Leoloso\GraphQLByPoPWPPlugin\PostTypes\GraphQLEndpointPostType;

/**
 * Base class for configuring the persisted GraphQL query before its execution
 */
abstract class AbstractGraphQLQueryConfigurator
{
    /**
     * Keep a map of all namespaced type names to their resolver classes
     *
     * @var array
     */
    protected $namespacedTypeNameClasses;
    /**
     * Keep a map of all directives names to their resolver classes
     *
     * @var array
     */
    protected $directiveNameClasses;

    /**
     * If executing a persisted GraphQL query, then initialize the configuration of different involved services
     *
     * @return void
     */
    public function init(): void
    {
        if (\is_singular(GraphQLQueryPostType::POST_TYPE) || \is_singular(GraphQLEndpointPostType::POST_TYPE)) {
            $this->doInit();
        }
    }

    /**
     * Function to override, to initialize the configuration of services before the execution of the GraphQL query
     *
     * @return void
     */
    abstract protected function doInit(): void;

    /**
     * Obtain the ID from the configuration custom post types, containing the configuration for the GraphQL query
     *
     * @param string $metaKey Under what meta key is the ID stored
     * @return mixed The ID of the configuration custom post
     */
    protected function getConfigurationCustomPostID(string $metaKey)
    {
        global $post;
        $graphQLQueryPost = $post;
        do {
            $aclPostID = \get_post_meta($graphQLQueryPost->ID, $metaKey, true);
            // If it doesn't have an ACL defined, and it has a parent, check if it has an ACL, then use that one
            if (!$aclPostID && $graphQLQueryPost->post_parent) {
                $graphQLQueryPost = \get_post($graphQLQueryPost->post_parent);
            } else {
                // Make sure to exit the `while` for the root post, even if it doesn't have ACL
                $graphQLQueryPost = null;
            }
        } while (!$aclPostID && !is_null($graphQLQueryPost));

        return $aclPostID;
    }

    /**
     * Read the configuration post, and extract the configuration, contained through the specified block
     *
     * @param string $configurationPostID
     * @param AbstractBlock $block
     * @return void
     */
    protected function getBlocksOfTypeFromConfigurationCustomPost(string $configurationPostID, AbstractBlock $block)
    {
        $configurationPost = \get_post($configurationPostID);
        $blocks = \parse_blocks($configurationPost->post_content);
        // Obtain the blocks of "Access Control" type
        $blockFullName = $block->getBlockFullName();
        return array_filter(
            $blocks,
            function ($block) use ($blockFullName) {
                return $block['blockName'] == $blockFullName;
            }
        );
    }

    /**
     * Lazy load and return the `$namespacedTypeNameClasses` array
     *
     * @return array
     */
    protected function getNamespacedTypeNameClasses(): array
    {
        if (is_null($this->namespacedTypeNameClasses)) {
            $this->initNamespacedTypeNameClasses();
        }
        return $this->namespacedTypeNameClasses;
    }
    /**
     * Initialize the `$namespacedTypeNameClasses` array
     *
     * @return void
     */
    protected function initNamespacedTypeNameClasses(): void
    {
        $instanceManager = InstanceManagerFacade::getInstance();
        $typeRegistry = TypeRegistryFacade::getInstance();
        $typeResolverClasses = $typeRegistry->getTypeResolverClasses();
        // For each class, obtain its namespacedTypeName
        $this->namespacedTypeNameClasses = [];
        foreach ($typeResolverClasses as $typeResolverClass) {
            $typeResolver = $instanceManager->getInstance($typeResolverClass);
            $typeResolverNamespacedName = $typeResolver->getNamespacedTypeName();
            $this->namespacedTypeNameClasses[$typeResolverNamespacedName] = $typeResolverClass;
        }
    }

    /**
     * Lazy load and return the `$directiveNameClasses` array
     *
     * @return array
     */
    protected function getDirectiveNameClasses(): array
    {
        if (is_null($this->directiveNameClasses)) {
            $this->initDirectiveNameClasses();
        }
        return $this->directiveNameClasses;
    }
    /**
     * Initialize the `$directiveNameClasses` array
     *
     * @param string $selectedField
     * @param [type] $value
     * @return array
     */
    protected function initDirectiveNameClasses(): void
    {
        $instanceManager = InstanceManagerFacade::getInstance();
        $directiveRegistry = DirectiveRegistryFacade::getInstance();
        $directiveResolverClasses = $directiveRegistry->getDirectiveResolverClasses();
        // For each class, obtain its directive name. Notice that different directives
        // can have the same name (eg: @translate as implemented for Google and Azure),
        // then the mapping goes from name to list of resolvers
        $this->directiveNameClasses = [];
        foreach ($directiveResolverClasses as $directiveResolverClass) {
            $directiveResolver = $instanceManager->getInstance($directiveResolverClass);
            $directiveResolverName = $directiveResolver->getDirectiveName();
            $this->directiveNameClasses[$directiveResolverName][] = $directiveResolverClass;
        }
    }

    /**
     * Create a service configuration entry comprising a field and its value
     * It returns a single array (or null)
     *
     * @param string $selectedField
     * @param mixed $value
     * @return array|null
     */
    protected function getEntryFromField(string $selectedField, $value): ?array
    {
        $namespacedTypeNameClasses = $this->getNamespacedTypeNameClasses();
        // The field is composed by the type namespaced name, and the field name, separated by "."
        // Extract these values
        $entry = explode(BlockConstants::TYPE_FIELD_SEPARATOR_FOR_DB, $selectedField);
        $namespacedTypeName = $entry[0];
        $field = $entry[1];
        // From the type, obtain which resolver class processes it
        if ($typeResolverClass = $namespacedTypeNameClasses[$namespacedTypeName]) {
            // Check `getConfigurationEntries` to understand format of each entry
            return [$typeResolverClass, $field, $value];
        }
        return null;
    }
    /**
     * Create the service configuration entries comprising a directive and its value
     * It returns an array of arrays
     *
     * @param string $selectedField
     * @param mixed $value
     * @return array|null
     */
    protected function getEntriesFromDirective(string $selectedDirective, $value): ?array
    {
        $directiveNameClasses = $this->getDirectiveNameClasses();
        // Obtain the directive resolver class from the directive name.
        // If more than one resolver has the same directive name, add all of them
        if ($selectedDirectiveResolverClasses = $directiveNameClasses[$selectedDirective]) {
            $entriesForDirective = [];
            foreach ($selectedDirectiveResolverClasses as $directiveResolverClass) {
                $entriesForDirective[] = [$directiveResolverClass, $value];
            }
            return $entriesForDirective;
        }
        return null;
    }
}
