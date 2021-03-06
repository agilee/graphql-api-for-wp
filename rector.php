<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Set\ValueObject\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/vendor/getpop/*/src',
        __DIR__ . '/vendor/pop-schema/*/src',
        __DIR__ . '/vendor/graphql-by-pop/*/src',
    ]);

    // is there a file you need to skip?
    $parameters->set(Option::EXCLUDE_PATHS, [
        __DIR__ . '/vendor/getpop/migrate-*/*',
        __DIR__ . '/vendor/pop-schema/migrate-*/*',
        __DIR__ . '/vendor/graphql-by-pop/graphql-parser/*',
    ]);

    // here we can define, what sets of rules will be applied
    $parameters->set(Option::SETS, [
        // @todo Uncomment when PHP 8.0 released
        // SetList::DOWNGRADE_PHP80,
        SetList::DOWNGRADE_PHP74,
        SetList::DOWNGRADE_PHP73,
        SetList::DOWNGRADE_PHP72,
    ]);

    // is your PHP version different from the one your refactor to? [default: your PHP version]
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.1');

    // Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
    $parameters->set(Option::AUTOLOAD_PATHS, [
        // full directory
        __DIR__ . '/vendor/wordpress/wordpress',
    ]);

    /**
     * This constant is defined in wp-load.php, but never loaded.
     * It is read when resolving class WP_Upgrader in Plugin.php.
     * Define it here again, or otherwise Rector fails with message:
     *
     * "PHP Warning:  Use of undefined constant ABSPATH -
     * assumed 'ABSPATH' (this will throw an Error in a future version of PHP)
     * in .../graphql-api-for-wp/vendor/wordpress/wordpress/wp-admin/includes/class-wp-upgrader.php
     * on line 13"
     */
    /** Define ABSPATH as this file's directory */
    if (!defined('ABSPATH')) {
        define('ABSPATH', __DIR__ . '/vendor/wordpress/wordpress/');
    }
};
