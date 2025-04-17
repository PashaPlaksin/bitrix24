<?php

namespace Logger\Absence;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use CIBlockElement;
use Exception;

class AbsenceEventsHandlers
{
    public static string $MODULE_ID = "loggerabsence";

    /**
     * Обработчик перед удалением элемента.
     * @param int $ID
     * @return true
     * @throws Exception
     */
    public static function onBeforeElementDelete(int $ID): bool
    {

        if (self::isElementFromAbsenceIblock($ID)) {
            (new AbsenceEventLogger(self::getIblockId()))->onDelete($ID);
        }
        return true;
    }

    /**
     * Обработчик после добавления элемента.
     * @param array $arFields
     * @throws Exception
     */
    public static function onAfterElementAdd(array $arFields): void
    {
        if (!self::isValidIblock($arFields)) {
            return;
        }

        if (!empty($arFields['ID'])) {
            (new absenceeventlogger(self::getIblockId()))->onAdd($arFields['ID']);
        }

    }

    /**
     * Обработчик после обновления элемента.
     * @param array $arFields
     * @throws Exception
     */
    public static function onAfterElementUpdate(array $arFields): void
    {
        if (!self::isValidIblock($arFields)) {
            return;
        }

        if (!empty($arFields['ID'])) {
            (new absenceeventlogger(self::getIblockId()))->onUpdate($arFields['ID']);
        }
    }

    /**
     * Проверка перед добавлением/обновлением элемента.
     * @param array $arFields
     * @return false|void
     */
    public static function onBeforeElementAddOrUpdate(array $arFields)
    {
        global $APPLICATION;

        if (!self::isValidIblock($arFields)) {
            return;
        }
        if (!self::checkAbsenceOverlap($arFields)) {
            $APPLICATION->throwException(Loc::getMessage('EVENT_DUPLICATE'));
            return false;
        }
    }


    /**
     * Проверка на пересечение с другими событиями.
     * @param array $arFields
     * @return bool
     */
    private static function checkAbsenceOverlap(array $arFields): bool
    {
        $employeeId = current($arFields['PROPERTY_VALUES'][1]);
        $absenceStart = $arFields['ACTIVE_FROM'];
        $absenceEnd = $arFields['ACTIVE_TO'];
        $type = current($arFields['PROPERTY_VALUES'][4]);

        if (empty($employeeId) || empty($absenceStart) || empty($absenceEnd)) {
            return true;
        }

        if ($type === self::getAbsenceType()) {
            return true;
        }

        $arFilter = [
            'IBLOCK_ID' => self::getIblockId(),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => $employeeId,
            '!ID' => $arFields['ID'] ?? false,
            [
                "LOGIC" => "OR",
                [
                    ">=DATE_ACTIVE_FROM" => $absenceStart, // начало периода
                    "<=DATE_ACTIVE_FROM" => $absenceEnd, // конец периода
                ],
                [
                    ">=DATE_ACTIVE_TO" => $absenceStart,
                    "<=DATE_ACTIVE_TO" => $absenceEnd,
                ],
            ]
        ];

        $res = CIBlockElement::GetList([], $arFilter, false, false, ['ID'])->fetch();
        file_put_contents($_SERVER['DOCUMENT_ROOT']. '/upload/log.txt', print_r($res,true), FILE_APPEND);
        return empty($res);
    }

    /** Получение идентификатора инфоблока из настроек
     * @return int
     */
    private static function getIblockId(): int
    {
        return (int)Option::get(self::$MODULE_ID, 'IBLOCK_ID');
    }

    /** Получение исключающего события из проверки пересечения
     * @return string
     */
    private static function getAbsenceType(): string
    {
        return (int)Option::get(self::$MODULE_ID, 'ABSENCE_TYPE');
    }

    /** Проверка идентификатора инфоблока
     * @param array $arFields
     * @return bool
     */
    private static function isValidIblock(array $arFields): bool
    {
        return (int)$arFields['IBLOCK_ID'] === self::getIblockId();
    }

    /** Проверка принадлежности элемента к инфоблоку
     * @param int $ID
     * @return bool
     */
    private static function isElementFromAbsenceIblock(int $ID): bool
    {
        // без D7, так как нет заранее сгенерированного ORM-класса
        $res = CIBlockElement::GetList(
            [],
            ['ID' => $ID, 'IBLOCK_ID' => self::getIblockId()],
            false,
            false,
            ['ID']
        );

        return (bool)$res->Fetch();
    }




}
