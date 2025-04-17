<?php
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

$arComponentDescription = [
    "NAME" => Loc::getMessage('COMPONENT_NAME'),
    "DESCRIPTION" => Loc::getMessage('COMPONENT_DESCRIPTION'),
    "SORT" => 10,
    "PATH" => [
        "ID" => "custom",
        "NAME" => Loc::getMessage('COMPONENT_PATH_NAME'),
    ],
];