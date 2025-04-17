<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\SystemException;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Logger\Absence\Service\AbsenceLogTable;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserTable;

/**
 * Компонент отображения событий отсутствия в виде грида.
 */
class AbsenceEventLoggerGridComponent extends CBitrixComponent implements Controllerable
{
    /** @var int ID HL-блока */
    private int $hlblockId;

    /** @var string ID модуля */
    private string $MODULE_ID = 'loggerabsence';

    /** @var string ID инфоблока подразделений */
    private string $IBLOCK_ID;

    /** Конструктор
     * @throws LoaderException
     * @throws SystemException
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        if (!Loader::includeModule($this->MODULE_ID)) {
            throw new SystemException(Loc::getMessage('INCLUDE_MODULE_ERROR'));
        }

    }
    /** Конфигурация действий для компонента
     *
     * @return array
     */
    public function configureActions(): array
    {
        return [];
    }

    /**
     * Основной метод компонента.
     * @throws LoaderException
     * @throws SystemException
     */
    public function executeComponent()
    {
        if (!Loader::includeModule("highloadblock") || !Loader::includeModule($this->MODULE_ID)) {
            return;
        }

        $this->arResult["GRID_ID"] = "highloadblock_list";
        $this->getHighLoadBlockId();
        $this->IBLOCK_ID = Option::get($this->MODULE_ID, 'IBLOCK_ID_DEPARTMENT');

        $fieldsRes = $this->getTypeEntity();
        $uiFilter = [];

        foreach ($fieldsRes as $value) {
            $addProperty = [
                'id' => $value['FIELD_NAME'],
                'name' => $value['XML_ID'],
            ];

            switch ($value['USER_TYPE_ID']) {
                case 'datetime':
                    $addProperty += [
                        'type' => 'date',
                        'time' => true,
                        'params' => ['multiple' => $value['MULTIPLE']],
                    ];
                    break;

                case 'enumeration':
                    $addProperty += [
                        'type' => 'list',
                        'params' => ['multiple' => 'Y'],
                        'items' => $this->getEnumFields($value['ID']),
                    ];
                    break;

                case 'string':
                    $addProperty += [
                        'type' => 'text',
                        'default' => true,
                    ];
                    break;

                case 'employee':
                    $addProperty += [
                        'type' => 'entity_selector',
                        'params' => [
                            'multiple' => 'Y',
                            'dialogOptions' => [
                                'height' => 240,
                                'context' => 'filter',
                                'entities' => [
                                    ['id' => 'user', 'options' => ['inviteEmployeeLink' => false]],
                                    ['id' => 'department'],
                                ]
                            ],
                        ],
                    ];
                    break;

                case 'integer':
                    $addProperty += [
                        'type' => 'list',
                        'default' => true,
                        'params' => ['multiple' => 'Y'],
                        'items' => $this->getIblockSections(),
                    ];
                    break;
            }

            $uiFilter[] = $addProperty;
        }

        $this->arResult["FILTER"] = $uiFilter;

        $this->arResult["COLUMNS"] = array_map(function ($filterField) {
            return [
                "id" => $filterField["id"],
                "name" => $filterField["name"],
                "sort" => $filterField["id"],
                "default" => true
            ];
        }, $this->arResult["FILTER"]);

        $this->prepareData();

        $this->includeComponentTemplate();
    }

    /**
     * Метод получение ID HL-блока на основе таблицы из ORM
     *
     * @return void
     */
    private function getHighLoadBlockId(): void
    {
        $tableName = AbsenceLogTable::getEntity()->getDBTableName();

        $hlblock = HL\HighloadBlockTable::getList([
            'filter' => ['=TABLE_NAME' => $tableName],
        ])->fetch();

        $this->hlblockId = (int)$hlblock['ID'];
    }

    /**
     * Метод подготовки данных для отображения в гриде
     *
     * @throws SystemException
     */
    private function prepareData(): void
    {
        $hlblock = HL\HighloadBlockTable::getById($this->hlblockId)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();

        $gridOptions = new GridOptions($this->arResult["GRID_ID"]);
        $navParams = $gridOptions->GetNavParams();
        $pageSize = $navParams["nPageSize"] ?? 20;

        $nav = new PageNavigation("page");
        $nav->allowAllRecords(true)
            ->setPageSizes([5, 10, 20, 50, 100])
            ->setPageSize($pageSize)
            ->initFromUri();

        $filterOption = new FilterOptions($this->arResult["GRID_ID"]);
        $filterData = $filterOption->getFilter([]);
        $filter = $this->getFilter($filterData);
        $sorting = $gridOptions->getSorting();

        $result = $entityClass::getList([
            'filter' => $filter,
            'order' => $sorting['sort'],
            'limit' => $nav->getLimit(),
            'offset' => $nav->getOffset(),
        ]);
        $this->arResult["NAV_OBJECT"] = $nav;
        $this->arResult["ROWS"] = $this->getGridList($result);
    }

    /**
     * Метод преобразует результат выборки HL-блока в формат для грида.
     *
     * @param iterable $res
     * @return array
     * @throws SystemException
     */
    private function getGridList(iterable $res): array
    {
        $list = [];

        foreach ($res as $row) {
            $tmpArray = [];

            foreach ($row as $key => $value) {
                $textValue = $this->getEnumerationValue($key, $value)
                    ?? $this->getEmployeeValue($key, $value)
                    ?? $this->getDepartmentValue($key, $value)
                    ?? $value;

                $tmpArray[$key] = $textValue;
            }

            $list[]['data'] = $tmpArray;
        }

        return $list;
    }

    /**
     * Метод получение значения для поля типа enumeration.
     *
     * @param string $key
     * @param mixed $value
     * @return string|null
     */
    private function getEnumerationValue(string $key, $value): ?string
    {
        $result = \CUserTypeEntity::GetList([], [
            'ENTITY_ID' => 'HLBLOCK_' . $this->hlblockId,
            'USER_TYPE_ID' => 'enumeration',
            'FIELD_NAME' => $key
        ]);

        while ($field = $result->fetch()) {
            $enum = \CUserFieldEnum::GetList([], ["USER_FIELD_ID" => $field['ID'], "ID" => $value]);
            if ($enumVal = $enum->fetch()) {
                return $enumVal['VALUE'];
            }
        }

        return null;
    }

    /**
     * Метод получение значения сотрудника
     *
     * @param string $key
     * @param mixed $value
     * @return string|null
     * @throws SystemException
     */
    private function getEmployeeValue(string $key, mixed $value): ?string
    {
        $result = \CUserTypeEntity::GetList([], [
            'ENTITY_ID' => 'HLBLOCK_' . $this->hlblockId,
            'USER_TYPE_ID' => 'employee',
            'FIELD_NAME' => $key
        ]);

        while ($field = $result->fetch()) {
            try {
                $user = UserTable::getList([
                    'select' => ['ID', 'NAME', 'LAST_NAME'],
                    'filter' => ['=ID' => $value],
                    'limit' => 1,
                ])->fetch();

                if ($user) {
                    $profileLink = "/company/personal/user/{$user['ID']}/";
                    $profileName = htmlspecialcharsbx($user['LAST_NAME'] . ' ' . $user['NAME']);
                    return "<a href='{$profileLink}'>{$profileName}</a>";
                }
            } catch (\Exception $e) {
                throw new SystemException(Loc::getMessage('USER_ID_ERROR',['#VALUE#' => $value]));
            }
        }

        return null;
    }

    /**
     * Метод получает название подразделения по ID
     *
     * @param string $key
     * @param mixed $value
     * @return string|null
     */
    private function getDepartmentValue(string $key, mixed $value): ?string
    {
        if ($key !== 'UF_DEPARTMENT' || empty($value)) {
            return null;
        }

        $sections = \CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $this->IBLOCK_ID, 'ACTIVE' => 'Y', 'ID' => $value],
            false,
            ['ID', 'NAME']
        );

        if ($section = $sections->Fetch()) {
            return $section['NAME'];
        }

        return null;
    }

    /**
     * Метод получение всех пользовательских полей HL-блока
     *
     * @return array
     */
    private function getTypeEntity(): array
    {
        $fieldsRes = [];
        $userTypeEntity = new CUserTypeEntity();
        $result = $userTypeEntity->GetList([], ['ENTITY_ID' => 'HLBLOCK_' . $this->hlblockId]);

        while ($res = $result->fetch()) {
            $fieldsRes[] = $res;
        }

        return $fieldsRes;
    }

    /**
     * Метод получение значений списка (enum) по ID поля
     *
     * @param int $id
     * @return array
     */
    private function getEnumFields(int $id): array
    {
        $fieldsRes = [];
        $result = \CUserFieldEnum::GetList([], ["USER_FIELD_ID" => $id]);

        while ($res = $result->fetch()) {
            $fieldsRes[$res['ID']] = $res['VALUE'];
        }

        return $fieldsRes;
    }

    /**
     * Метод получение списка отделов из инфоблока.
     *
     * @return array
     */
    private function getIblockSections(): array
    {
        $result = [];
        $sections = \CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $this->IBLOCK_ID, 'ACTIVE' => 'Y'],
            false,
            ['ID', 'NAME']
        );

        while ($section = $sections->Fetch()) {
            $result[$section['ID']] = $section['NAME'];
        }

        return $result;
    }

    /**
     * Метод преобразует фильтр UI в фильтр ORM
     *
     * @param array $filterData
     * @return array
     */
    public function getFilter(array $filterData): array
    {
        $filter = [];

        $map = [
            'UF_DATE_CREATE' => ['>=UF_DATE_CREATE', '<=UF_DATE_CREATE'],
            'UF_ACTIVE_FROM' => ['>=UF_ACTIVE_FROM', '<=UF_ACTIVE_FROM'],
            'UF_ACTIVE_TO' => ['>=UF_ACTIVE_TO', '<=UF_ACTIVE_TO'],
        ];

        foreach ($map as $field => [$fromKey, $toKey]) {
            if (!empty($filterData["{$field}_from"])) {
                $filter[$fromKey] = $filterData["{$field}_from"];
            }
            if (!empty($filterData["{$field}_to"])) {
                $filter[$toKey] = $filterData["{$field}_to"];
            }
        }

        $exactFields = [
            'UF_EVENT_TYPE', 'UF_OPERATION_TYPE',
            'UF_MODIFY_BY', 'UF_CREATED_BY', 'UF_DEPARTMENT'
        ];

        foreach ($exactFields as $field) {
            if (!empty($filterData[$field])) {
                $filter["={$field}"] = $filterData[$field];
            }
        }

        if (!empty($filterData['UF_SEARCHABLE_CONTENT'])) {
            $filter['%UF_SEARCHABLE_CONTENT'] = $filterData['UF_SEARCHABLE_CONTENT'];
        }

        if (!empty($filterData['FIND'])) {
            $filter['%UF_SEARCHABLE_CONTENT'] = $filterData['FIND'];
        }

        return $filter;
    }
}
