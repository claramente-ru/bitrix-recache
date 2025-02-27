<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Claramente\Recache\Entity\ClaramenteRecacheUrlsTable;
use Bitrix\Main\UI\AdminPageNavigation;
use Claramente\Recache\Services\RecacheService;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

if (!Loader::includeModule('claramente.recache')) {
    throw new Exception('Необходимо установить модуль claramente.recache');
}

/** @global $APPLICATION CMain */
global $APPLICATION;

$APPLICATION->SetTitle('Страницы для регенерации кэша');
$request = Application::getInstance()->getContext()->getRequest();

// Генерация таблицы
$sTableID = ClaramenteRecacheUrlsTable::getTableName();
$oSort = new CAdminSorting($sTableID, 'CREATED_AT', 'asc');
$lAdmin = new CAdminList($sTableID, $oSort);

// Заголовки таблицы
$headers = [
    ['id' => 'CREATED_AT', 'content' => 'Дата добавления', 'sort' => 'CREATED_AT', 'default' => true],
    ['id' => 'UPDATED_AT', 'content' => 'Дата обновления', 'sort' => 'UPDATED_AT', 'default' => true],
    ['id' => 'SITE_ID', 'content' => 'Сайт', 'sort' => 'SITE_ID', 'default' => true],
    ['id' => 'URL', 'content' => 'Ссылка', 'sort' => 'URL', 'default' => true],
    ['id' => 'STATUS', 'content' => 'Статус', 'sort' => 'STATUS', 'default' => true],
    ['id' => 'RESPONSE_CODE', 'content' => 'Код ответа', 'sort' => 'RESPONSE_CODE', 'default' => true],
    ['id' => 'REQUEST_TIME_MS', 'content' => 'Время ответа', 'sort' => 'REQUEST_TIME_MS', 'default' => true],
];
$lAdmin->AddHeaders($headers);

// Вывод сообщений из параметров
if ($request->getQuery('message')) {
    if ($request->getQuery('message_type') === 'success') {
        $lAdmin->AddActionSuccessMessage($request->getQuery('message'));
    } else {
        $lAdmin->AddGroupError($request->getQuery('message'));
    }
}

// Действия с регенерацией страниц
if ($request->get('mode') !== 'list') {
    // Сообщения действия
    $redirectMessage = null; // Выводимое сообщение
    $messageType = 'success'; // Тип сообщения success|error

    // Старт новой регенерации
    if ($request->get('start_recache') === 'Y') {
        $recacheService = new RecacheService();
        $recache = $recacheService->regenerateUrls();
        if ($recache['success']) {
            $redirectMessage = 'Страницы успешно сгенерированы';
        } else {
            $redirectMessage = 'Ошибка генерации. ' . $recache['error'];
            $messageType = 'error';
        }
    }
    // Отмена необработанных элементов
    if ($request->get('cancel_recache') === 'Y') {
        ClaramenteRecacheUrlsTable::cancelWaitingUrls();
        $redirectMessage = 'Очередь успешно отменена';
    }
    // Очистка очереди
    if ($request->get('clear_recache') === 'Y') {
        ClaramenteRecacheUrlsTable::clearTable();
        $redirectMessage = 'Страницы успешно удалены';
    }

    // Необходимость редиректа после выполнения действия
    if (null !== $redirectMessage) {
        $redirectQuery = [
            'lang' => LANG,
            'message' => $redirectMessage,
            'message_type' => $messageType,
        ];
        LocalRedirect($request->getRequestedPage() . '?' . http_build_query($redirectQuery));
    }
}

// Получение данных для таблицы
$getListParams = [
    'select' => ['*'],
    'order' => [$request->get('by') ?: 'CREATED_AT' => $request->get('order') ?: 'ASC'],
];
// Пагинация
$nav = new AdminPageNavigation('nav');
$nav->allowAllRecords(true)
    ->setPageSize(20)
    ->initFromUri();
$getListParams['count_total'] = true;
$getListParams['offset'] = $nav->getOffset();
$getListParams['limit'] = $nav->getLimit();

$rsData = ClaramenteRecacheUrlsTable::getList($getListParams);
$nav->setRecordCount($rsData->getCount());

// Связь навигации с таблицей
$lAdmin->setNavigation($nav, 'Страницы');

// Заполнение строк таблицы
while ($arRes = $rsData->Fetch()) {
    $row = $lAdmin->AddRow($arRes['ID'], $arRes);
}

// Контекстное меню
$aContext = [
    [
        'TEXT' => 'Регенерировать все страницы',
        'ONCLICK' => 'if (confirm(\'Запустить регенерацию страниц? Текущие страницы будут удалены\')) window.location.href=\'claramente_recache_pages.php?start_recache=Y&lang=' . LANG.'\';',
        'ICON' => 'btn_new'
    ],
    [
        'TEXT' => 'Отменить обработку необработанных страниц',
        'ONCLICK' => 'if (confirm(\'Отменить обработку необработанных страниц?\')) window.location.href=\'claramente_recache_pages.php?cancel_recache=Y&lang=' . LANG.'\';',
    ],
    [
        'TEXT' => 'Очистить все страницы',
        'ONCLICK' => 'if (confirm(\'Очистить очередь? Текущие страницы будут удалены\')) window.location.href=\'claramente_recache_pages.php?clear_recache=Y&lang=' . LANG.'\';',
        'ICON' => 'btn_delete'
    ]
];

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

// Важно установить prolog_admin_after после генерации данных страниц, но до ее вывода
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$lAdmin->DisplayList();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
