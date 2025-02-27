<?php
declare(strict_types=1);

/**
 * Административные методы для модуля claramente.recache
 */
final class ClaramenteModuleRecache
{
    /**
     * Отображение модуля в глобальном меню сайта
     * @param array $adminMenu
     * @param array $moduleMenu
     * @return void
     */
    public static function onBuildGlobalMenu(array &$adminMenu, array &$moduleMenu): void
    {
        global $APPLICATION;
        if ($APPLICATION->GetGroupRight('claramente.recache') >= 'R') {
            $items = [
                'parent_menu' => 'global_menu_services',
                'section' => 'rating',
                'sort' => 50,
                'text' => 'Регенерация кэширования',
                'title' => 'Регенерация кэширования',
                'icon' => 'refresh_menu_icon',
                'page_icon' => 'update_page_icon',
                'items_id' => 'recache_menu',
                'items' => [
                    [
                        'text' => 'Настройки модуля',
                        'title' => 'Настройки модуля',
                        'sort' => 100,
                        'url' => '/bitrix/admin/claramente_recache.php?lang=' . LANG,
                        'items_id' => 'main',
                        'icon' => 'util_menu_icon',
                        'page_icon' => 'util_menu_icon',
                    ],
                    [
                        'text' => 'Страницы регенерации',
                        'title' => 'Страницы регенерации',
                        'sort' => 200,
                        'url' => '/bitrix/admin/claramente_recache_pages.php?lang=' . LANG,
                        'items_id' => 'main',
                        'icon' => 'update_menu_icon',
                        'page_icon' => 'update_page_icon',
                    ]
                ]
            ];

            $moduleMenu[] = $items;
        }
    }
}
