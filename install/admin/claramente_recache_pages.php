<?php

if (is_file($_SERVER['DOCUMENT_ROOT'] . '/local/modules/claramente.recache/admin/pages.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/claramente.recache/admin/pages.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/claramente.recache/admin/pages.php';
}
