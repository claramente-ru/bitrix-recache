<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Context;

// Подключаемые классы
$classes = [
    'ClaramenteModuleRecache' => 'classes/general/claramentemodulerecache.php',
];

// Возможно класс уже подключен
if (! class_exists('ClaramenteRecacheProcessAgent')) {
    $classes['ClaramenteRecacheProcessAgent'] = 'classes/claramenterecacheprocessagent.php';
}

Loader::registerAutoLoadClasses('claramente.recache', $classes);

// Стили
if (Context::getCurrent()->getRequest()->isAdminSection()) {
    global $APPLICATION;
    $APPLICATION->SetAdditionalCSS('/local/modules/claramente.recache/assets/css/admin_style.css');
}