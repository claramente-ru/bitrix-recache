<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

/**
 * Установочный класс модуля claramente.recache
 */
class claramente_recache extends CModule
{
    // ID модуля
    public const MODULE_ID = 'claramente.recache';

    // Опции для настройки
    public const OPTIONS = [
        [
            'NAME' => 'recache_enable', // Включить модуль
            'VALUE' => 'Y'
        ],
        [
            'NAME' => 'recache_start_period', // Регенерировать страницы каждые (минуты)
            'VALUE' => 60
        ],
        [
            'NAME' => 'recache_exec', // Последний запуск регенерации
            'VALUE' => null
        ],
        [
            'NAME' => 'sitemap_url', // Sitemap URL
            'VALUE' => null
        ],
        [
            'NAME' => 'request_timeout', // Таймаут запроса
            'VALUE' => 5
        ],
        [
            'NAME' => 'request_agent_limit', // Ограничение запросов на агент за раз
            'VALUE' => 100
        ],
        [
            'NAME' => 'request_timeout_between_requests', // Таймаут между запросами
            'VALUE' => 0
        ],
        [
            'NAME' => 'request_timeout_step', // Шаг-интервал таймаута между запросами
            'VALUE' => 15
        ]
    ];

    function __construct()
    {
        // Агенты
        if (!class_exists('ClaramenteRecacheProcessAgent')) {
            require __DIR__ . '/../classes/claramenterecacheprocessagent.php';
        }

        require __DIR__ . '/version.php';
        /**
         * @var array $version
         */
        $this->MODULE_ID = $this->GetModuleId();
        $this->PARTNER_NAME = 'Claramente';
        $this->PARTNER_URI = 'https://claramente.ru';
        $this->MODULE_VERSION = $version['VERSION'];
        $this->MODULE_VERSION_DATE = $version['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('CLAREMENTE_MODULE_RECACHE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('CLAREMENTE_MODULE_RECACHE_DESCRIPTION');
    }

    /**
     * @return string
     */
    public function GetModuleId(): string
    {
        return self::MODULE_ID;
    }

    /**
     * @return bool
     */
    public function DoInstall(): bool
    {
        global $APPLICATION, $DB;
        // Регистрация событий
        $this->registerEventHandlers();

        // Копированием файлов для административной панели
        CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');

        // Добавим таблицы модуля
        $connection = Application::getConnection();
        $dbInstall = $DB->RunSQLBatch(__DIR__ . '/db/' . $connection->getType() . '/install.sql');
        if (is_array($dbInstall)) {
            // Ошибка установки БД
            $APPLICATION->ThrowException(implode(',', $dbInstall));

            return false;
        }

        // Добавим агент
        if (! ClaramenteRecacheProcessAgent::addAgent()) {
            // Ошибка установки БД
            $APPLICATION->ThrowException('Ошибка добавления агента. ' . $APPLICATION->GetException()->GetString());

            return false;
        }

        // Установим опции
        $this->setOptions();

        // Все шаги выполнены успешно, регистрируем модуль
        ModuleManager::RegisterModule(self::GetModuleId());

        return true;
    }

    /**
     * @return bool
     */
    public function DoUninstall(): bool
    {
        // Удаление событий
        $this->unRegisterEventHandlers();

        // Удаление файлов из административной панели
        DeleteDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');

        // Все шаги выполнены успешно, удаляем модуль
        ModuleManager::UnRegisterModule(self::GetModuleId());

        // Удалим агенты
        CAgent::RemoveModuleAgents($this::MODULE_ID);

        // Удалим опции
        Option::delete($this::MODULE_ID);

        return true;
    }

    /**
     * @return void
     */
    public function registerEventHandlers()
    {
        $moduleId = self::GetModuleId();
        require_once __DIR__ . '/event_handlers/register.php';
    }

    /**
     * @return void
     */
    public function unRegisterEventHandlers()
    {
        $moduleId = self::GetModuleId();
        require_once __DIR__ . '/event_handlers/unregister.php';
    }

    /**
     * Установить options для всех сайтов
     * @return void
     */
    public function setOptions(): void
    {
        $sites = CSite::GetList();
        // Установим настройки для каждого сайта
        while ($site = $sites->Fetch()) {
            foreach ($this::OPTIONS as $option) {
                Option::set($this::MODULE_ID, $option['NAME'], $option['VALUE'], $site['ID']);
            }
            // Установим sitemap_url по умолчанию для сайта
            $sitemapUrl = sprintf('https://%s/sitemap.xml',$site['SERVER_NAME']);
            Option::set($this::MODULE_ID, 'sitemap_url', $sitemapUrl, $site['ID']);
        }
    }
}
