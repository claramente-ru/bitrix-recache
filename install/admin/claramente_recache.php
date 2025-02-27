<?php

if (is_file($_SERVER['DOCUMENT_ROOT'] . '/local/modules/claramente.recache/admin/index.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/claramente.recache/admin/index.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/claramente.recache/admin/index.php';
}
