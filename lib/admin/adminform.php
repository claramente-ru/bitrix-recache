<?php
declare(strict_types=1);

namespace Claramente\Recache\Admin;

use CAdminForm;

/**
 * Административные методы для модуля claramente.recache
 */
final class AdminForm
{
    /**
     * Получить вкладки
     * @return array
     */
    public function getMainFormTabs(): array
    {
        $tabs = [];
        $tabs[] = $this->collectTab('📚 Справочники', 'hlblocks');
        // Вкладка доступов
        $tabs[] = $this->collectTab('🧑‍🧑‍🧒‍🧒️️ Права доступов', 'rights');
        // Вкладка для настроек tabs
        $tabs[] = $this->collectTab('🗂️ Секции', 'sections');
        // Вкладка о нас
        $tabs[] = $this->collectTab(name: 'ℹ️ О модуле', div: 'about', sort: 999_999_999);

        return $tabs;
    }

    /**
     * Экземпляр построения административной панели
     * @param string $name
     * @param array $tabs
     * @param bool $canExpand
     * @param bool $denyAutosave
     * @return CAdminForm
     */
    public function getForm(
        string $name = 'tabControl',
        array  $tabs = [],
        bool   $canExpand = true,
        bool   $denyAutosave = false
    ): CAdminForm
    {
        return new CAdminForm(
            $name,
            $tabs,
            $canExpand,
            $denyAutosave
        );
    }

    /**
     * Формирование Tab
     * @param string $name
     * @param string $div
     * @param int|null $id
     * @param int $sort
     * @param bool $required
     * @param string $icon
     * @param string|null $code
     * @return array @see CAdminForm
     */
    public function collectTab(
        string $name,
        string $div,
        ?int   $id = null,
        int    $sort = 100,
        bool   $required = true,
        string $icon = 'fileman',
        string $code = null
    ): array
    {
        return [
            'CODE' => $code,
            'ID' => $id,
            'SORT' => $sort,
            'TAB' => $name,
            'ICON' => $icon,
            'TITLE' => $name,
            'DIV' => $div,
            'required' => $required
        ];
    }
}
