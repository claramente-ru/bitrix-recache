<?php
declare(strict_types=1);

namespace Claramente\Recache\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Claramente\Recache\Structures\Entity\RecacheUrlStructure;
use Bitrix\Main\Entity;

/**
 * Таблица - claramente_recache_urls.
 * Здесь хранятся ссылки для обработки
 */
final class ClaramenteRecacheUrlsTable extends DataManager
{
    /**
     * Название таблицы
     * @return string
     */
    public static function getTableName(): string
    {
        return 'claramente_recache_urls';
    }

    /**
     * Поля таблицы
     * @return array
     */
    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'is_autocomplete' => true
            ]),
            new StringField('SITE_ID', [
                'nullable' => true,
                'size' => 16
            ]),
            new StringField('URL', [
                'nullable' => false,
                'size' => 2083
            ]),
            new StringField('STATUS', [
                'nullable' => 'wait',
                'size' => 16
            ]),
            new StringField('URL_HASH', [
                'nullable' => false,
                'size' => 32
            ]),
            new IntegerField('RESPONSE_CODE', [
                'default_value' => null,
                'nullable' => true
            ]),
            new IntegerField('REQUEST_TIME_MS', [
                'default_value' => null,
                'nullable' => false
            ]),
            new DatetimeField('CREATED_AT'),
            new DatetimeField('UPDATED_AT')
        ];
    }

    /**
     * Добавление хэша для URL
     * @param Event $event
     * @return Entity\EventResult
     */
    public static function onBeforeAdd(Event $event)
    {
        $result = new Entity\EventResult();
        $fields = $event->getParameter('fields');
        $result->modifyFields(
            [
                'URL_HASH' => md5($fields['URL'] ?? '')
            ]
        );

        return $result;
    }

    /**
     * Добавление хэша для URL
     * @param Event $event
     * @return Entity\EventResult
     */
    public static function onBeforeUpdate(Event $event)
    {
        return self::onBeforeAdd($event);
    }

    /**
     * Получить элемент по URL
     * @param string $url
     * @return RecacheUrlStructure|null
     */
    public static function getByUrl(string $url): ?array
    {
        $hash = md5($url);
        $data = self::query()
            ->setSelect(['*'])
            ->setFilter([
                '=URL_HASH' => $hash
            ])
            ->fetch();

        return $data ? RecacheUrlStructure::fromArray($data) : null;
    }

    /**
     * Взять первые необработанные ссылки
     * @param int $limit
     * @param string|null $siteId
     * @return RecacheUrlStructure[]
     */
    public static function getFirstUnprocessedUrls(int $limit = 1000, ?string $siteId = null): array
    {
        $filter = [
            '=STATUS' => 'wait'
        ];
        if (null !== $siteId) {
            $filter['=SITE_ID'] = $siteId;
        }
        $result = [];
        $urls = self::query()
            ->setSelect(['*'])
            ->setLimit($limit)
            ->setFilter($filter)
            ->setOrder([
                'ID' => 'ASC'
            ])
            ->fetchAll();
        foreach ($urls as $data) {
            $result[] = RecacheUrlStructure::fromArray($data);
        }

        return $result;
    }

    /**
     * Очистить всю таблицу
     * @param string|null $siteId
     * @return void
     */
    public static function clearTable(?string $siteId = null): void
    {
        global $DB;
        if (null !== $siteId) {
            $DB->Query(sprintf('DELETE FROM %s WHERE SITE_ID = \'%s\'', self::getTableName(), $siteId));
        } else {
            $DB->Query('DELETE FROM ' . self::getTableName());
        }
    }

    /**
     * Отменить выполнение элементов в очереди
     * @return void
     */
    public static function cancelWaitingUrls(): void
    {
        global $DB;
        $DB->Query(sprintf('UPDATE %s SET STATUS = \'cancel\' WHERE STATUS = \'wait\'', self::getTableName()));
    }
}
