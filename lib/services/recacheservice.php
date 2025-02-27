<?php

namespace Claramente\Recache\Services;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Claramente\Recache\Entity\ClaramenteRecacheUrlsTable;
use CSite;

/**
 * Основной класс работы с модулем
 */
final class RecacheService
{
    /**
     * Сервис для работы с sitemap
     * @var SitemapUrlService
     */
    private SitemapUrlService $sitemapUrlService;

    public function __construct()
    {
        $this->sitemapUrlService = new SitemapUrlService();
    }

    /**
     * Генерация URL
     * @param string|null $siteId SITE_ID. Null - для всех
     * @return array
     */
    public function regenerateUrls(?string $siteId = null): array
    {
        $result = [
            'success' => true,
            'error' => null,
        ];

        $siteUrls = [];

        // Шаг 1: Получаем ссылки для обхода по каждому сайту
        $filter = [];
        if ($siteId) {
            $filter['SITE_ID'] = $siteId;
        }
        $sites = CSite::GetList(arFilter: $filter);
        while ($site = $sites->Fetch()) {
            // Проверка, включен ли модуль для этого сайта
            if (Option::get('claramente.recache', 'recache_enable', 'N', $site['SITE_ID']) !== 'Y') {
                continue;
            }
            // Sitemap для текущего сайта
            $sitemapUrls = Option::get('claramente.recache', 'sitemap_url', null, $site['SITE_ID']);
            if (! $sitemapUrls) {
                continue;
            }
            foreach (explode(',', $sitemapUrls) as $sitemapUrl) {
                $siteUrls[$site['SITE_ID']] = $this->sitemapUrlService->getSitemapUrls(trim($sitemapUrl));

            }
            // Обновим последний запуск регенерации сайта
            Option::set('claramente.recache', 'recache_exec', (new DateTime())->toString(), $site['SITE_ID']);

            // Очищаем существующую очередь
            ClaramenteRecacheUrlsTable::clearTable($site['SITE_ID']);
        }

        // Не собрали список ссылок
        if (! $siteUrls) {
            $result['success'] = false;
            $result['error'] = 'Не получено ни одной ссылки из sitemap. Проверьте настройки, активность модуля и ссылки на sitemap';

            return $result;
        }

        // Шаг 2: Добавим записи в очередь
        foreach ($siteUrls as $siteId => $urls) {
            foreach ($urls as $url) {
                ClaramenteRecacheUrlsTable::add([
                    'URL' => $url,
                    'SITE_ID' => $siteId
                ]);
            }
        }

        return $result;
    }

    /**
     * Запуск процесса обхода ссылок
     * @return array
     */
    public function process(): array
    {
        $success = 0;
        $fail = 0;

        $counter = 0;
        // Пройдемся по каждому сайту
        $sites = CSite::GetList();
        // У каждого сайта свои настройки
        while ($site = $sites->Fetch()) {
            // Лимит запросов
            $limit = Option::get('claramente.recache', 'recache_agent_limit', 100, $site['SITE_ID']);
            // Таймаут запроса
            $timeout = Option::get('claramente.recache', 'request_timeout', 3, $site['SITE_ID']);
            // Таймаут между запросами в секундах
            $timeoutBetweenRequests = Option::get('claramente.recache', 'request_timeout_between_requests', 0, $site['SITE_ID']);
            // Шаг для тайм-аута (таймаут выполняется после этого шага)
            $timeoutStep = Option::get('claramente.recache', 'request_timeout_step', 15, $site['SITE_ID']);

            // Получим первые необработанные ссылки
            foreach (ClaramenteRecacheUrlsTable::getFirstUnprocessedUrls($limit) as $url) {
                $counter++;
                // Таймер запроса
                $timer = microtime(true);
                try {
                    $httpClient = new HttpClient();
                    $httpClient->setTimeout($timeout)->get($url->url);
                    $statusCode = $httpClient->getStatus();
                } catch (\Exception $e) {
                    $statusCode = 400;
                }
                // Сохраним результат в БД
                $url->requestTimeMs = intval((microtime(true) - $timer) * 1000);
                $url->responseCode = $statusCode;
                $url->status = 'done';
                $url->save();
                // Запишем результат
                if ($url->responseCode === 200) {
                    $success++;
                } else {
                    $fail++;
                }

                // Таймаут при необходимости
                if ($timeoutBetweenRequests && $counter % $timeoutStep === 0) {
                    sleep($timeoutBetweenRequests);
                }
            }
        }


        return [
            'success' => $success,
            'fail' => $fail
        ];
    }
}
