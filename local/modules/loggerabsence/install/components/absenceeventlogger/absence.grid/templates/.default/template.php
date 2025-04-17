<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Grid;
use Bitrix\UI\Toolbar\Facade\Toolbar;


$columns = [];
foreach ($arResult["COLUMNS"] as $column) {
    $columns[] = [
        "id" => $column["id"],
        "name" => $column["name"],
        "sort" => $column["id"],
        "default" => true
    ];
}

Toolbar::addFilter([
    'FILTER_ID' => $arResult["GRID_ID"],
    'GRID_ID' => $arResult["GRID_ID"],
    'FILTER' => $arResult["FILTER"],
    'ENABLE_LIVE_SEARCH' => true,
    'ENABLE_FIELDS_SEARCH' => true,
    'ENABLE_LABEL' => true,
]);

$APPLICATION->IncludeComponent(
    "bitrix:main.ui.grid",
    "",
    [
        'GRID_ID' => $arResult["GRID_ID"],
        'COLUMNS' => $columns,
        'ROWS' => $arResult["ROWS"],
        'SHOW_ROW_CHECKBOXES' => true,
        'NAV_OBJECT' => $arResult["NAV_OBJECT"],
        'TOTAL_ROWS_COUNT' => count($arResult["ROWS"]),
        'AJAX_MODE' => 'Y',
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'PAGE_SIZES' => [
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100']
        ],
        'AJAX_OPTION_JUMP' => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => true,
        'SHOW_ROW_ACTIONS_MENU' => true,
        'SHOW_GRID_SETTINGS_MENU' => true,
        'SHOW_NAVIGATION_PANEL' => true,
        'SHOW_PAGINATION' => true,
        'SHOW_SELECTED_COUNTER' => true,
        'SHOW_TOTAL_COUNTER' => true,
        'SHOW_PAGESIZE' => true,
        'SHOW_ACTION_PANEL' => true,
        'ALLOW_COLUMNS_SORT' => true,
        'ALLOW_COLUMNS_RESIZE' => true,
        'ALLOW_HORIZONTAL_SCROLL' => true,
        'ALLOW_SORT' => true,
        'ALLOW_PIN_HEADER' => true,
        'AJAX_OPTION_HISTORY' => 'N',
    ],
    $component
);
