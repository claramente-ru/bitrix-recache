<?php

use Bitrix\Main\Config\Option;
use Claramente\Recache\Services\RecacheService;
use Bitrix\Main\Type\DateTime;

/**
 * Агент для модуля claramente.recache
 */
class ClaramenteRecacheProcessAgent
{
    public const MODULE_ID = 'claramente.recache';
    public const NAME = 'ClaramenteRecacheProcessAgent::execute();';

    /**
     * Запуск агента для генерации кэша
     * @return string
     */
    public static function execute(): string
    {
        $recache = new RecacheService();

        // Регенерация страниц
        $sites = CSite::GetList();
        while ($site = $sites->GetNext()) {
            // Регенерация сайта включена
            $recacheSiteEnable = Option::get(self::MODULE_ID, 'recache_enable', 'N', $site['SITE_ID']) === 'Y';
            if (! $recacheSiteEnable) {
                continue;
            }
            // Периодичность запуска регенерации в минутах
            $periodSiteRecache = (int)Option::get(self::MODULE_ID, 'recache_start_period', 0, $site['SITE_ID']);
            if (! $periodSiteRecache) {
                continue;
            }
            // Последняя дата регенерации сайта
            $recacheSiteExec = Option::get(self::MODULE_ID, 'recache_exec', null, $site['SITE_ID']);
            if (! $recacheSiteExec || strtotime($recacheSiteExec) < strtotime("- {$periodSiteRecache} minutes")) {
                $recache->regenerateUrls($site['SITE_ID']);
            }
        }

        // Старт обхода страниц
        $recache->process();

        return self::NAME;
    }

    /**
     * Проверка существования агента
     * @return bool
     */
    public static function agentExists(): bool
    {
        return boolval(self::getAgent());
    }

    /**
     * Получить агента
     * @return array|null
     */
    public static function getAgent(): ?array
    {
        $agent = CAgent::GetList(arFilter: [
            'NAME' => self::NAME,
            'MODULE_ID' => self::MODULE_ID
        ])->Fetch();

        return $agent ? $agent : null;
    }

    /**
     * Создать агент
     * @return bool
     */
    public static function addAgent(): bool
    {
        if (self::agentExists()) {
            return true;
        }
        $date = DateTime::createFromTimestamp(time() + 60);

        return (bool)CAgent::AddAgent(
            name: self::NAME,
            module: self::MODULE_ID,
            interval: 60,
            next_exec: $date->toString()
        );
    }

    /**
     * Удалить агент
     * @return bool
     */
    public static function deleteAgent(): bool
    {
        if (! self::agentExists()) {
            return true;
        }
        CAgent::RemoveAgent(self::NAME, self::MODULE_ID);

        return true;
    }
}
