<?php

namespace Logger\Absence\Service;


use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\SystemException;


/**
 * Class AbsenceLogTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_CREATED_BY int optional
 * <li> UF_MODIFY_BY int optional
 * <li> UF_OPERATION_TYPE int optional
 * <li> UF_DEPARTMENT int optional
 * <li> UF_SEARCHABLE_CONTENT text optional
 * <li> UF_EVENT_TYPE int optional
 * <li> UF_ACTIVE_FROM datetime optional
 * <li> UF_ACTIVE_TO datetime optional
 * <li> UF_DATE_CREATE datetime optional
 * </ul>
 *
 * @package AbsenceLogTable
 **/

class AbsenceLogTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return 'hl_absence_events';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('EVENTS_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_CREATED_BY',
                [
                    'title' => Loc::getMessage('EVENTS_ENTITY_UF_CREATED_BY_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_MODIFY_BY',
                [
                    'title' => Loc::getMessage('EVENTS_ENTITY_UF_MODIFY_BY_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_OPERATION_TYPE',
                [
                    'title' => Loc::getMessage('EVENTS_ENTITY_UF_OPERATION_TYPE_FIELD'),
                ]
            ),
            new TextField(
                'UF_SEARCHABLE_CONTENT',
                [
                    'title' => Loc::getMessage('EVENTS_ENTITY_UF_SEARCHABLE_CONTENT_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_EVENT_TYPE',
                [
                    'title' => Loc::getMessage('EVENTS_ENTITY_UF_EVENT_TYPE_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_DEPARTMENT',
                [
                    'title' => Loc::getMessage('EVENTS_ENTITY_UF_DEPARTMENT_FIELD'),
                ]
            ),
            new DatetimeField(
                'UF_ACTIVE_FROM',
                [
                    'title' => Loc::getMessage('EVENTS_ENTITY_UF_ACTIVE_FROM_FIELD'),
                ]
            ),
            new DatetimeField(
                'UF_ACTIVE_TO',
                [
                    'title' => Loc::getMessage('EVENTS_ENTITY_UF_ACTIVE_TO_FIELD'),
                ]
            ),
            new DatetimeField(
                'UF_DATE_CREATE',
                [
                    'title' => Loc::getMessage('EVENTS_ENTITY_UF_DATE_CREATE_FIELD'),
                ]
            ),
        ];
    }
}