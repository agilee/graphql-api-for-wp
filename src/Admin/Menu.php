<?php
namespace Leoloso\GraphQLByPoPWPPlugin\Admin;

use Leoloso\GraphQLByPoPWPPlugin\Admin\SettingsMenuPage;
use Leoloso\GraphQLByPoPWPPlugin\Admin\GraphiQLMenuPage;

/**
 * Admin menu class
 */
class Menu {
    /**
     * Initialize the endpoints
     *
     * @return void
     */
    public function init(): void
    {
        add_action(
            'admin_menu',
            [$this, 'addMenuPages']
        );
    }
    function addMenuPages(): void
    {
        add_menu_page(
            __('GraphQL by PoP', 'graphql-by-pop'),
            __('GraphQL by PoP', 'graphql-by-pop'),
            'manage_options',
            'graphql_by_pop',
            '',
            'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxOC4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkdyYXBoUUxfTG9nbyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4Ig0KCSB5PSIwcHgiIHZpZXdCb3g9IjAgMCA0MDAgNDAwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCA0MDAgNDAwIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxnPg0KCQk8Zz4NCgkJCQ0KCQkJCTxyZWN0IHg9IjEyMiIgeT0iLTAuNCIgdHJhbnNmb3JtPSJtYXRyaXgoLTAuODY2IC0wLjUgMC41IC0wLjg2NiAxNjMuMzE5NiAzNjMuMzEzNikiIGZpbGw9IiNFNTM1QUIiIHdpZHRoPSIxNi42IiBoZWlnaHQ9IjMyMC4zIi8+DQoJCTwvZz4NCgk8L2c+DQoJPGc+DQoJCTxnPg0KCQkJPHJlY3QgeD0iMzkuOCIgeT0iMjcyLjIiIGZpbGw9IiNFNTM1QUIiIHdpZHRoPSIzMjAuMyIgaGVpZ2h0PSIxNi42Ii8+DQoJCTwvZz4NCgk8L2c+DQoJPGc+DQoJCTxnPg0KCQkJDQoJCQkJPHJlY3QgeD0iMzcuOSIgeT0iMzEyLjIiIHRyYW5zZm9ybT0ibWF0cml4KC0wLjg2NiAtMC41IDAuNSAtMC44NjYgODMuMDY5MyA2NjMuMzQwOSkiIGZpbGw9IiNFNTM1QUIiIHdpZHRoPSIxODUiIGhlaWdodD0iMTYuNiIvPg0KCQk8L2c+DQoJPC9nPg0KCTxnPg0KCQk8Zz4NCgkJCQ0KCQkJCTxyZWN0IHg9IjE3Ny4xIiB5PSI3MS4xIiB0cmFuc2Zvcm09Im1hdHJpeCgtMC44NjYgLTAuNSAwLjUgLTAuODY2IDQ2My4zNDA5IDI4My4wNjkzKSIgZmlsbD0iI0U1MzVBQiIgd2lkdGg9IjE4NSIgaGVpZ2h0PSIxNi42Ii8+DQoJCTwvZz4NCgk8L2c+DQoJPGc+DQoJCTxnPg0KCQkJDQoJCQkJPHJlY3QgeD0iMTIyLjEiIHk9Ii0xMyIgdHJhbnNmb3JtPSJtYXRyaXgoLTAuNSAtMC44NjYgMC44NjYgLTAuNSAxMjYuNzkwMyAyMzIuMTIyMSkiIGZpbGw9IiNFNTM1QUIiIHdpZHRoPSIxNi42IiBoZWlnaHQ9IjE4NSIvPg0KCQk8L2c+DQoJPC9nPg0KCTxnPg0KCQk8Zz4NCgkJCQ0KCQkJCTxyZWN0IHg9IjEwOS42IiB5PSIxNTEuNiIgdHJhbnNmb3JtPSJtYXRyaXgoLTAuNSAtMC44NjYgMC44NjYgLTAuNSAyNjYuMDgyOCA0NzMuMzc2NikiIGZpbGw9IiNFNTM1QUIiIHdpZHRoPSIzMjAuMyIgaGVpZ2h0PSIxNi42Ii8+DQoJCTwvZz4NCgk8L2c+DQoJPGc+DQoJCTxnPg0KCQkJPHJlY3QgeD0iNTIuNSIgeT0iMTA3LjUiIGZpbGw9IiNFNTM1QUIiIHdpZHRoPSIxNi42IiBoZWlnaHQ9IjE4NSIvPg0KCQk8L2c+DQoJPC9nPg0KCTxnPg0KCQk8Zz4NCgkJCTxyZWN0IHg9IjMzMC45IiB5PSIxMDcuNSIgZmlsbD0iI0U1MzVBQiIgd2lkdGg9IjE2LjYiIGhlaWdodD0iMTg1Ii8+DQoJCTwvZz4NCgk8L2c+DQoJPGc+DQoJCTxnPg0KCQkJDQoJCQkJPHJlY3QgeD0iMjYyLjQiIHk9IjI0MC4xIiB0cmFuc2Zvcm09Im1hdHJpeCgtMC41IC0wLjg2NiAwLjg2NiAtMC41IDEyNi43OTUzIDcxNC4yODc1KSIgZmlsbD0iI0U1MzVBQiIgd2lkdGg9IjE0LjUiIGhlaWdodD0iMTYwLjkiLz4NCgkJPC9nPg0KCTwvZz4NCgk8cGF0aCBmaWxsPSIjRTUzNUFCIiBkPSJNMzY5LjUsMjk3LjljLTkuNiwxNi43LTMxLDIyLjQtNDcuNywxMi44Yy0xNi43LTkuNi0yMi40LTMxLTEyLjgtNDcuN2M5LjYtMTYuNywzMS0yMi40LDQ3LjctMTIuOA0KCQlDMzczLjUsMjU5LjksMzc5LjIsMjgxLjIsMzY5LjUsMjk3LjkiLz4NCgk8cGF0aCBmaWxsPSIjRTUzNUFCIiBkPSJNOTAuOSwxMzdjLTkuNiwxNi43LTMxLDIyLjQtNDcuNywxMi44Yy0xNi43LTkuNi0yMi40LTMxLTEyLjgtNDcuN2M5LjYtMTYuNywzMS0yMi40LDQ3LjctMTIuOA0KCQlDOTQuOCw5OSwxMDAuNSwxMjAuMyw5MC45LDEzNyIvPg0KCTxwYXRoIGZpbGw9IiNFNTM1QUIiIGQ9Ik0zMC41LDI5Ny45Yy05LjYtMTYuNy0zLjktMzgsMTIuOC00Ny43YzE2LjctOS42LDM4LTMuOSw0Ny43LDEyLjhjOS42LDE2LjcsMy45LDM4LTEyLjgsNDcuNw0KCQlDNjEuNCwzMjAuMyw0MC4xLDMxNC42LDMwLjUsMjk3LjkiLz4NCgk8cGF0aCBmaWxsPSIjRTUzNUFCIiBkPSJNMzA5LjEsMTM3Yy05LjYtMTYuNy0zLjktMzgsMTIuOC00Ny43YzE2LjctOS42LDM4LTMuOSw0Ny43LDEyLjhjOS42LDE2LjcsMy45LDM4LTEyLjgsNDcuNw0KCQlDMzQwLjEsMTU5LjQsMzE4LjcsMTUzLjcsMzA5LjEsMTM3Ii8+DQoJPHBhdGggZmlsbD0iI0U1MzVBQiIgZD0iTTIwMCwzOTUuOGMtMTkuMywwLTM0LjktMTUuNi0zNC45LTM0LjljMC0xOS4zLDE1LjYtMzQuOSwzNC45LTM0LjljMTkuMywwLDM0LjksMTUuNiwzNC45LDM0LjkNCgkJQzIzNC45LDM4MC4xLDIxOS4zLDM5NS44LDIwMCwzOTUuOCIvPg0KCTxwYXRoIGZpbGw9IiNFNTM1QUIiIGQ9Ik0yMDAsNzRjLTE5LjMsMC0zNC45LTE1LjYtMzQuOS0zNC45YzAtMTkuMywxNS42LTM0LjksMzQuOS0zNC45YzE5LjMsMCwzNC45LDE1LjYsMzQuOSwzNC45DQoJCUMyMzQuOSw1OC40LDIxOS4zLDc0LDIwMCw3NCIvPg0KPC9nPg0KPC9zdmc+DQo='
        );

        add_submenu_page(
            'graphql_by_pop',
            __('GraphiQL', 'graphql-by-pop'),
            __('GraphiQL', 'graphql-by-pop'),
            'manage_options',
            'graphql_by_pop',
            [new GraphiQLMenuPage(), 'print']
        );

        add_submenu_page(
            'graphql_by_pop',
            __('Interactive schema', 'graphql-by-pop'),
            __('Interactive schema', 'graphql-by-pop'),
            'manage_options',
            'graphql_by_pop_voyager',
            [new GraphQLVoyagerMenuPage(), 'print']
        );

        add_submenu_page(
            'graphql_by_pop',
            __('Settings', 'graphql-by-pop'),
            __('Settings', 'graphql-by-pop'),
            'manage_options',
            'graphql_by_pop_settings',
            [new SettingsMenuPage(), 'print']
        );

        add_submenu_page(
            'graphql_by_pop',
            __('Documentation', 'graphql-by-pop'),
            __('Documentation', 'graphql-by-pop'),
            'manage_options',
            'graphql_by_pop_documentation',
            [$this, 'printDocumentation']
        );

        // if (current_user_can('manage_options')) {
        //     global $submenu;
        //     $submenu['graphql_by_pop'][] = [
        //         __('Documentation', 'graphql-by-pop'),
        //         'manage_options',
        //         'https://github.com/getpop/graphql',
        //     ];
        // }
    }

    function printGraphiQLPage() {
        if (!current_user_can('manage_options'))  {
            wp_die(__( 'You do not have sufficient permissions to access this page.'));
        }
        echo 'GraphiQL!';
    }

    function printDocumentation() {
        if (!current_user_can('manage_options'))  {
            wp_die(__( 'You do not have sufficient permissions to access this page.'));
        }
        echo 'Documentation';
    }

    function printOptionsPage() {
        if (!current_user_can('manage_options'))  {
            wp_die(__( 'You do not have sufficient permissions to access this page.'));
        }
        echo '<div class="wrap">';
        echo '<p>Here is where the form would go if I actually had options.</p>';
        echo '</div>';
    }
}
