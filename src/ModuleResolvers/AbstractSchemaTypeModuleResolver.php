<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\ModuleResolvers;

use GraphQLAPI\GraphQLAPI\ModuleTypeResolvers\ModuleTypeResolver;

abstract class AbstractSchemaTypeModuleResolver extends AbstractModuleResolver
{
    /**
     * The type of the module
     *
     * @param string $module
     * @return string
     */
    public function getModuleType(string $module): string
    {
        return ModuleTypeResolver::SCHEMA_TYPE;
    }
}
