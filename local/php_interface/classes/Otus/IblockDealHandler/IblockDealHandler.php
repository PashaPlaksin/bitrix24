<?php
namespace Otus\IblockDealHandler;

use Bitrix\Iblock\Elements\ElementOrderTable;
use Bitrix\Crm\DealTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;

class IblockDealHandler
{
    private const TARGET_IBLOCK_ID = 27; // Заявки
    protected static bool $handlerDisallow = false;

    public static function registerEvents(): void
    {
        if (!Loader::includeModule("iblock") || !Loader::includeModule("crm")) {
            return;
        }

        AddEventHandler('iblock', 'OnAfterIBlockElementAdd', [self::class, "onIBlockElementChange"]);
        AddEventHandler('iblock', 'OnAfterIBlockElementUpdate', [self::class, "onIBlockElementChange"]);
        AddEventHandler('iblock', 'OnBeforeIBlockElementDelete', [self::class, "onIBlockElementDelete"]);
        AddEventHandler('crm', 'OnAfterCrmDealUpdate', [self::class, "onCrmDealChange"]);
    }

    public static function onIBlockElementChange($arFields): void
    {
        if ($arFields['IBLOCK_ID'] != self::TARGET_IBLOCK_ID) {
            return;
        }

        $propertyValues = self::getPropertyValue($arFields);
        if (!$propertyValues) return;

        $dealId = $propertyValues['DEAL_ID'] ?? null;
        if (!$dealId) return;

        $currentDeal = DealTable::getById($dealId)->fetch();
        if (!$currentDeal) return;

        $fieldsToUpdate = [];

        if ($currentDeal['ASSIGNED_BY_ID'] != $propertyValues['RESPONSIBLE']) {
            $fieldsToUpdate['ASSIGNED_BY_ID'] = $propertyValues['RESPONSIBLE'];
        }
        if ($currentDeal['TITLE'] != $arFields['NAME']) {
            $fieldsToUpdate['TITLE'] = $arFields['NAME'];
        }
        if ($currentDeal['OPPORTUNITY'] != $propertyValues['SUM']) {
            $fieldsToUpdate['OPPORTUNITY'] = $propertyValues['SUM'];
        }

        if (!empty($fieldsToUpdate)) {
            DealTable::update($dealId, $fieldsToUpdate);
        }
    }

    private static function getPropertyValue($propertyData): array
    {
        $propertyIds = array_keys($propertyData['PROPERTY_VALUES']);

        $properties = PropertyTable::getList([
            'filter' => ['ID' => $propertyIds],
            'select' => ['ID', 'CODE']
        ])->fetchAll();

        $propertyCodes = array_column($properties, 'CODE', 'ID');

        $propertyValues = [];
        foreach ($propertyData['PROPERTY_VALUES'] as $propertyId => $value) {
            $code = $propertyCodes[$propertyId] ?? null;
            if ($code) {
                $propertyValues[$code] = is_array($value) ? reset($value)['VALUE'] ?? reset($value) : $value;
            }
        }

        return $propertyValues;
    }

    public static function onCrmDealChange($arFields): void
    {
        $dealId = $arFields['ID'] ?? null;
        if (!$dealId) return;

        $elements = ElementOrderTable::getList([
            'filter' => ['IBLOCK_ID' => self::TARGET_IBLOCK_ID, '=D_VALUE' => $dealId],
            'select' => ['ID', 'NAME', 'D_' => 'DEAL_ID', 'S_' => 'SUM', 'RES_' => 'RESPONSIBLE']
        ]);

        while ($element = $elements->fetch()) {
            $fieldsToUpdate = [];
            $name = $element['NAME'];
            $fieldsToUpdate['SUM'] = $element['S_VALUE'];
            $fieldsToUpdate['RESPONSIBLE'] = $element['RES_VALUE'];
            $fieldsToUpdate['DEAL_ID'] = $dealId;
            $change = false;

            if (isset($arFields['TITLE']) && $element['NAME'] != $arFields['TITLE']) {
                $name = $arFields['TITLE'];
                $change = true;
            }
            if (isset($arFields['OPPORTUNITY']) && $element['S_VALUE'] != $arFields['OPPORTUNITY']) {
                $fieldsToUpdate['SUM'] = $arFields['OPPORTUNITY'];
                $change = true;
            }
            if (isset($arFields['ASSIGNED_BY_ID']) && $element['RES_VALUE'] != $arFields['ASSIGNED_BY_ID']) {
                $fieldsToUpdate['RESPONSIBLE'] = $arFields['ASSIGNED_BY_ID'];
                $change = true;
            }

            if ($change) {
                $res = new \CIBlockElement();
                $res->Update($element['ID'], ['NAME' => $name, 'PROPERTY_VALUES' => $fieldsToUpdate]);
            }
        }
    }
}
