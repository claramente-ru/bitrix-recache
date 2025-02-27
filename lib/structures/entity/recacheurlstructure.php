<?php
declare(strict_types=1);

namespace Claramente\Recache\Structures\Entity;

use Bitrix\Main\Type\DateTime;
use Claramente\Recache\Entity\ClaramenteRecacheUrlsTable;

/**
 * Структура объекта данных сущности @see ClaramenteRecacheUrlsTable
 */
final class RecacheUrlStructure
{
    /**
     * @param int $id
     * @param string|null $siteId
     * @param string $url
     * @param string $urlHash
     * @param string $status
     * @param int|null $responseCode
     * @param int|null $requestTimeMs
     * @param DateTime $createdAt
     * @param DateTime $updatedAt
     */
    public function __construct(
        public int      $id,
        public ?string  $siteId,
        public string   $url,
        public string   $urlHash,
        public string   $status,
        public ?int     $responseCode,
        public ?int     $requestTimeMs,
        public DateTime $createdAt,
        public DateTime $updatedAt,
    )
    {
    }

    /**
     * Объект из массива
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: intval($data['ID']),
            siteId: $data['SITE_ID'],
            url: $data['URL'],
            urlHash: $data['URL_HASH'],
            status: $data['STATUS'],
            responseCode: $data['RESPONSE_CODE'] ? intval($data['RESPONSE_CODE']) : null,
            requestTimeMs: $data['REQUEST_TIME_MS'] ? intval($data['REQUEST_TIME_MS']) : null,
            createdAt: $data['CREATED_AT'],
            updatedAt: $data['UPDATED_AT']
        );
    }

    /**
     * Обновить элемент
     * @return bool
     */
    public function save(): bool
    {
        // Запрос выполним в try, так как элемент может отсутствовать в момент обновления из-за вероятно долгих request запросов
        try {
            $update = ClaramenteRecacheUrlsTable::update(
                $this->id,
                [
                    'URL' => $this->url,
                    'STATUS' => $this->status,
                    'RESPONSE_CODE' => $this->responseCode,
                    'REQUEST_TIME_MS' => $this->requestTimeMs
                ]
            );
        } catch (\Exception) {
            return false;
        }

        return $update->isSuccess();
    }
}
