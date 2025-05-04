<?php

if (file_exists(__DIR__.'/classes/autoload.php')) {
    require_once __DIR__.'/classes/autoload.php';
}

use Bitrix\Main\EventManager;
use Otus\IblockDealHandler\IblockDealHandler;
use Otus\CustomRestHandler\CustomRestHandler;

$arJsConfig = array(
    'custom_main' => array(
        'js' => '/local/js/required.js',
    )
);

foreach ($arJsConfig as $ext => $arExt) {
    \CJSCore::RegisterExt($ext, $arExt);
}

IblockDealHandler::registerEvents();
CustomRestHandler::registerEvents();

\Bitrix\Main\UI\Extension::load('timeman.start-work-day');

Bitrix\Main\EventManager::getInstance()->AddEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    [
        'Otus\CustomFields\BookingField',
        'GetUserTypeDescription'
    ]
);