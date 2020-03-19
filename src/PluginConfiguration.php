<?php
namespace Leoloso\GraphQLByPoPWPPlugin;

use PoP\ComponentModel\AbstractComponentConfiguration;
use Leoloso\GraphQLByPoPWPPlugin\ComponentConfiguration;
use Leoloso\GraphQLByPoPWPPlugin\Environment;

class PluginConfiguration
{
    /**
     * Map the environment variables from the components, to WordPress wp-config.php constants
     *
     * @return array
     */
    public static function init(): void
    {
        // All the environment variables to override
        $mappings = [
            ComponentConfiguration::class => Environment::ADD_EXCERPT_AS_DESCRIPTION,
        ];
        // For each environment variable, see if it has been defined as a wp-config.php constant
        foreach ($mappings as $mappingClass => $mappingEnvVariable) {
            $hookName = AbstractComponentConfiguration::getHookName($mappingClass, $mappingEnvVariable);
            \add_filter(
                $hookName,
                [self::class, 'useWPConfigConstant'],
                10,
                3
            );
        }
    }

    /**
     * Override the value of an environment variable if it has been defined as a constant in wp-config.php, with the environment name prepended with "GRAPHQL_API_"
     *
     * @param [type] $value
     * @param [type] $class
     * @param [type] $envVariable
     * @return mixed
     */
    public static function useWPConfigConstant($value, $class, $envVariable)
    {
        $constantName = 'GRAPHQL_API_'.$envVariable;
        if (defined($constantName)) {
            return constant($constantName);
        }
        return $value;
    }
}
