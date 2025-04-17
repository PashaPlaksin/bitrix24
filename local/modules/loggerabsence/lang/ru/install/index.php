<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
//install
$MESS = [

    'ABSENCE_EVENT_LOGGER_MODULE_NAME' => 'График отсутствий: Логирование событий',
    'ABSENCE_EVENT_LOGGER_MODULE_DESC' => 'Модуль логирования событий в графике отсутствий с проверкой пересечения событий. ',
    'ABSENCE_EVENT_LOGGER_PARTNER_NAME' => '1CSOFT',
    'ABSENCE_EVENT_LOGGER_PARTNER_URI' => 'https://1csoft.by/',
    'ABSENCE_EVENT_LOGGER_INSTALL_ERROR_VERSION' => 'Минимальная версия 20.00.00',
    'ABSENCE_EVENT_LOGGER_INSTALL_ERROR_PHP' => 'Для установки модуля требуется PHP 8.0.0 или выше',
    'HL_BLOCK_MODULE_LOAD_ERROR' => 'Не удалось подключить модуль \'highloadblock\'',
    'STEP1' => 'Шаг 1: Настройка инфоблоков',
    'STEP2' => 'Шаг 2: Завершение установки',
    'STEP1_UNINSTALL' => 'Шаг 1: Вариант удаления',
    'STEP2_UNINSTALL' => 'Шаг 2: Завершение удаления',
    'MENU_ITEM_TITLE' => 'ГО Журнал операций',
    'CUSTOM_SECTION_ERROR' => 'Ошибка создания CustomSection для страницы: #ERROR#',
    'PAGE_ADD_ERROR' => 'Ошибка создания страницы: ',
];

// HL-block fields
$MESS['EMPLOYEE_EVENTS'] = 'Сотрудник события';
$MESS['LINK_TO_EMPLOYEE'] = 'Привязка к сотруднику';
$MESS['OPERATION_INITIATOR'] = 'Инициатор операции';
$MESS['OPERATION_TYPE'] = 'Тип операции';
$MESS['REASON_FOR_ABSENCE'] = 'Причина отсутствия';
$MESS['ABSENCE_TYPE'] = 'Тип отсутствия';
$MESS['START_ABSENCE_PERIOD'] = 'Начало периода отсутствия';
$MESS['END_ABSENCE_PERIOD'] = 'Окончание периода отсутствия';
$MESS['EVENT_DATE'] = 'Дата события';
$MESS['DEPARTMENT'] = 'Подразделение';

//HL-block enumeration fields data
$MESS['VACATION'] = 'отпуск ежегодный';
$MESS['ASSIGNMENT'] = 'командировка';
$MESS['LEAVESICK'] = 'больничный';
$MESS['LEAVEMATERINITY'] = 'отпуск декретный';
$MESS['LEAVEUNPAYED'] = 'отгул за свой счет';
$MESS['UNKNOWN'] = 'прогул';
$MESS['OTHER'] = 'другое';
$MESS['PERSONAL'] = 'персональные календари';
$MESS['ADD'] = 'Добавление';
$MESS['UPDATE'] = 'Изменение';
$MESS['DELETE'] = 'Удаление';
$MESS['HL_BLOCK_ADD_ERROR'] = 'Ошибка создания HL-блока: ';
$MESS['IBLOCK_ERROR_GET'] = 'Инфоблок \'absence\' не найден';
$MESS['ERROR_ABSENCE_TYPE_PROPERTY'] = 'Свойство \'ABSENCE_TYPE\' не найдено в инфоблоке';
$MESS['HL_BLOCK_DELETE_ERROR'] = 'Ошибка при удалении Highload блока.';
$MESS['HL_BLOCK_NOT_FOUND'] = 'Highload блок с таким именем не найден.';


