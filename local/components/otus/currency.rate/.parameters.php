<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Currency\CurrencyManager;

if (!Loader::includeModule('currency')) {
    return;
}

$arCurrencies = [];
$currencies = CurrencyManager::getCurrencyList();
foreach ($currencies as $currencyCode => $currencyName) {
    $arCurrencies[$currencyCode] = $currencyName;
}

$arComponentParameters = [
    "PARAMETERS" => [
        "CURRENCY" => [
            "PARENT" => "BASE",
            "NAME" => "Выбор валюты",
            "TYPE" => "LIST",
            "VALUES" => $arCurrencies,
            "DEFAULT" => "USD",
            "REFRESH" => "N",
        ],
    ],
];


