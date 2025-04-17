<?php

namespace Logger\Absence;

use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UserTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use CUserFieldEnum;
use CUserTypeEntity;
use Exception;

use Logger\Absence\Service\AbsenceLogTable;

class AbsenceEventLogger
{
    private int $iblockId;
    private int $elementId;
    private string $currentOperation;
    private int $user;
    private int $userDepartment;
    private int $hlblockId;

    /**
     * @param int $iblockId
     * @throws Exception
     */
    public function __construct(int $iblockId)
    {
        $this->iblockId = $iblockId;
        $this->user = $this->getCurrentUser();
        $this->userDepartment = $this->getUserDepartments();
    }

    /**
     * @throws Exception
     */
    public function onAdd(int $id): void
    {
        $this->handleEvent('ADD', $id);
    }

    /**
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function onUpdate(int $id): void
    {
        $this->handleEvent('UPDATE', $id);
    }

    /**
     * @throws Exception
     */
    public function onDelete(int $id): void
    {
        $this->handleEvent('DELETE', $id);
    }

    /**
     * @throws Exception
     */
    private function handleEvent(string $operation, int $id): void
    {
        global $APPLICATION;
        $this->currentOperation = $operation;
        $this->elementId = $id;
        try {
            $eventData = $this->prepareEventData();
            $this->logEvent($eventData);
        } catch (Exception $e) {
            $APPLICATION->ThrowException(Loc::getMessage('EVENT_ADD_ERROR', ['#ERROR#' => $e ->getMessage()]));
            //throw new Exception("Ошибка при записи события: ". implode(', ', $e->getMessage()));
        }
    }

    /** Метод добавления события в таблицу HL-блока через ORM
     *
     * @throws Exception
     */
    private function logEvent(array $data): void
    {
        global $APPLICATION;
        $result = AbsenceLogTable::add($data);

        if (!$result->isSuccess()) {
            $APPLICATION->ThrowException(Loc::getMessage('EVENT_ADD_ERROR', ['#ERROR#' => implode(', ', $result->getErrorMessages())]));
        }
    }

    /** Метод формирует массив данных для записи в HL блок
     *
     * @throws Exception
     */
    private function prepareEventData(): array
    {
        global $APPLICATION;
        $element = $this->fetchElement();
        if (!$element) {
            $APPLICATION->ThrowException(Loc::getMessage('ELEMENT_ERROR'));
        }

        $this->getHighLoadBlockId();

        $fieldId = $this->getUserTypeEntity('UF_OPERATION_TYPE');

        $eventData = [
            'UF_DATE_CREATE' => new DateTime(),
            'UF_CREATED_BY' => $element['CREATED_BY'],
            'UF_ACTIVE_FROM' => $element['ACTIVE_FROM'],
            'UF_ACTIVE_TO' => $element['ACTIVE_TO'],
            'UF_SEARCHABLE_CONTENT' => $element['SEARCHABLE_CONTENT'],
            'UF_MODIFY_BY' => $this->user,
            'UF_DEPARTMENT' => $this->userDepartment,
            'UF_OPERATION_TYPE' => $this->getEnumIdByXmlId($this->currentOperation,$fieldId),
        ];

        $properties = $this->fetchElementProperties();
        foreach ($properties as $property) {
            $codes = $this->getPropertyCodes($property['IBLOCK_PROPERTY_ID']);
            foreach ($codes as $code) {
                switch ($code['CODE']) {
                    case 'USER':
                        $eventData['UF_CREATED_BY'] = $property['VALUE'];
                        break;
                    case 'ABSENCE_TYPE':
                        $xmlId = $this->getXmlIdByEnumId($property['VALUE']);
                        $fieldId = $this->getUserTypeEntity('UF_EVENT_TYPE');
                        $eventData['UF_EVENT_TYPE'] = $this->getEnumIdByXmlId($xmlId,$fieldId);
                        break;
                }
            }
        }

        return $eventData;
    }

    /** Метод проверяет существование элемента события в инфоблоке
     *
     * @return array|null
     */
    private function fetchElement(): ?array
    {
        return ElementTable::getList([
            'select' => ['ID', 'CREATED_BY', 'SEARCHABLE_CONTENT', 'ACTIVE_FROM', 'ACTIVE_TO'],
            'filter' => ['IBLOCK_ID' => $this->iblockId, 'ID' => $this->elementId],
        ])->fetch();
    }

    /** Метод получает свойства элемента инфоблока
     *
     * @return array
     */
    private function fetchElementProperties(): array
    {
        return ElementPropertyTable::getList([
            'select' => ['ID', 'IBLOCK_PROPERTY_ID', 'VALUE', 'VALUE_ENUM'],
            'filter' => ['IBLOCK_ELEMENT_ID' => $this->elementId],
            'order' => ['ID' => 'ASC']
        ])->fetchAll();
    }

    /** Метод получает код свойства инфоблока
     *
     * @param int $propertyId
     * @return array
     */
    private function getPropertyCodes(int $propertyId): array
    {
        return PropertyTable::getList([
            'filter' => ['IBLOCK_ID' => $this->iblockId, 'ID' => $propertyId],
            'select' => ['ID', 'CODE']
        ])->fetchAll();
    }

    /** Метод получает XML_ID по идентификатору элемента во множественном поле
     *
     * @param int $enumId
     * @return string
     */
    private function getXmlIdByEnumId(int $enumId): string
    {
        $enum = PropertyEnumerationTable::getList([
            'filter' => ['ID' => $enumId],
            'select' => ['XML_ID']
        ])->fetch();

        return $enum['XML_ID'] ?? '';
    }

    /** Получаем по XML_ID и идентификатору пользовательского поля, ID из списка
     *
     * @param string $xmlId
     * @param string $fieldId
     * @return int
     */
    private function getEnumIdByXmlId(string $xmlId, string $fieldId): int
    {
        $enum = CUserFieldEnum::GetList([], ['XML_ID' => $xmlId, 'USER_FIELD_ID' => $fieldId])->Fetch();
        return $enum['ID'] ?? 0;
    }

    /** Метод получает идентификатор поля HL блока
     *
     * @param string $fieldName
     * @return mixed
     */
    private function getUserTypeEntity(string $fieldName)
    {

        $userTypeEntity = new CUserTypeEntity();
        $field = $userTypeEntity->GetList([], [
            'ENTITY_ID' => 'HLBLOCK_' . $this->hlblockId,
            'FIELD_NAME' => $fieldName
        ])->Fetch();

        return $field['ID'];
    }

    /** Метод получает идентификатор HL блока
     *
     * @return void
     */
    private function getHighLoadBlockId()
    {
        $tableName = AbsenceLogTable::getEntity()->getDBTableName();

        $hlblock = HighloadBlockTable::getList([
            'filter' => ['=TABLE_NAME' => $tableName],
        ])->fetch();

        $this->hlblockId = $hlblock['ID'];
    }

    /** Метод возвращает идентификатор пользователя
     *
     * @throws Exception
     */
    private function getCurrentUser()
    {
        return CurrentUser::get()->getId();
    }

    /** Метод получает подразделение пользователя
     *
     * @return int|mixed
     */
    private function getUserDepartments(): mixed
    {
        $userData = UserTable::getList([
            'filter' => ['=ID' => $this->user],
            'select' => ['UF_DEPARTMENT']
        ])->fetch();

        $departments = [];

        if (!empty($userData['UF_DEPARTMENT'])) {
            $departments = is_array($userData['UF_DEPARTMENT'])
                ? $userData['UF_DEPARTMENT']
                : [$userData['UF_DEPARTMENT']];
        }

        return $departments[0] ?? 0;
    }
}
