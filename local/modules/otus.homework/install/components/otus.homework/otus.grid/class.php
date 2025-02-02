<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Otus\Homework\OtusEntityTable;

class OtusHomeworkOtusGrid extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule("otus.homework")) {
            ShowError("Модуль otus.homework не установлен");
            return;
        }

        $entityId = $this->arParams['entityID'];
        $entityTypeId = $this->arParams['entityTypeID'];
        $this->arResult['DATA'] = OtusEntityTable::getList([
            'filter' => ['CRM_ENTITY_ID' => $entityId, 'CRM_ENTITY_TYPE' => $entityTypeId],
        ])->fetchAll();

        $this->includeComponentTemplate();
    }
}
