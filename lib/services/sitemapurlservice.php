<?php

namespace Claramente\Recache\Services;


use Bitrix\Main\Web\HttpClient;

/**
 * Сервис для взаимодействий с URL из sitemap
 */
class SitemapUrlService
{
    /**
     * @param string $sitemapUrl
     * @return array
     */
    public function getSitemapUrls(string $sitemapUrl): array
    {
        // Шаг 1: Получим sitemap
        $httpClient = new HttpClient();
        $response = $httpClient->get($sitemapUrl);
        $xml = simplexml_load_string($response);

        // Ошибка чтения ответа
        if (false === $xml) {
            return [];
        }

        // Шаг 2: Собираем URL
        $urls = [];

        // Sitemap содержит sitemaps, получим список вложенных sitemap
        if (property_exists($xml, 'sitemap')) {
            foreach ($xml->sitemap as $sitemap) {
                $urls = array_merge($urls, $this->getSitemapUrls((string)$sitemap->loc));
            }
        }
        // Sitemap содержит ссылки
        if (property_exists($xml, 'url')) {
            foreach ($xml->url as $url) {
                $urls[] = (string)$url->loc;
            }
        }

        return array_unique($urls);
    }
}
