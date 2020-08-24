<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\ModuleTypeResolvers;

interface ModuleTypeResolverInterface
{
    public static function getModuleTypesToResolve(): array;
    public function getSlug(string $moduleType): string;
    public function getName(string $moduleType): string;
}
