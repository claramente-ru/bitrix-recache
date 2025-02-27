<?php
/**
 * @var string $moduleId
 */

$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->unRegisterEventHandler(
    'main',
    'OnBuildGlobalMenu',
    $moduleId,
    'ClaramenteModuleRecache',
    'onBuildGlobalMenu'
);