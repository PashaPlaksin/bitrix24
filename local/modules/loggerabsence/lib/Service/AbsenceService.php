<?php

namespace Logger\Absence\Service;


use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\LoaderException;
use CIBlockPropertyEnum;

class AbsenceService
{

    protected ?int $iblockId = null;
    protected ?int $iblockIdDep = null;

    /** Конструктор
     *
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule('iblock');
        $iblockName = GetMessage('ABSENCE');
        $iblockNameDep = GetMessage('DEPARTMENT');
        $this->iblockId = $this->resolveIblockIdByName($iblockName);
        $this->iblockIdDep = $this->resolveIblockIdByName($iblockNameDep);

    }

    /** Метод возвращает идентификатор инфоблока по его имени
     *
     * @param string $name
     * @return int|null
     */
    protected function resolveIblockIdByName(string $name): ?int
    {
        $res = IblockTable::getList([
            'filter' => ['=NAME' => $name],
            'select' => ['ID']
        ])->fetch();

        return $res ? (int)$res['ID'] : null;
    }

    /** Метод возвращает идентификатор инфоблока Графика отсутствий
     *
     * @return int|null
     */
    public function getIblockId(): ?int
    {
        return $this->iblockId;
    }

    /** Метод возвращает идентификатор инфоблока Подразделения
     *
     * @return int|null
     */
    public function getIblockIdDep(): ?int
    {
        return $this->iblockIdDep;
    }

    /**
     * Метод получает список значений ENUM свойства ABSENCE_TYPE
     *
     * @return array
     */
    public function getAbsenceTypeEnumValues(): array
    {
        if (!$this->iblockId) {
            return [];
        }

        $result = [];

        // Получаем значения свойства ABSENCE_TYPE
        /*
         * D7 ORM не поддерживает свойства GetProperties() напрямую — без заранее сгенерированного ORM-класса
         */
        $propertyEnums = CIBlockPropertyEnum::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $this->iblockId, 'CODE' => 'ABSENCE_TYPE']
        );

        while ($enum = $propertyEnums->Fetch()) {
            $result[$enum['ID']] = $enum['VALUE'];
        }

        return $result;
    }

    /**
     * Получить все элементы инфоблока
     *
     * @return array
     */
    public function getAbsenceElements(): array
    {
        if (!$this->iblockId) {
            return [];
        }

        $result = [];

        $res = ElementTable::getList([
            'select' => ['ID', 'NAME', 'PROPERTY_ABSENCE_TYPE'],
            'filter' => ['IBLOCK_ID' => $this->iblockId, 'ACTIVE' => 'Y']
        ]);

        while ($item = $res->fetch()) {
            $result[] = $item;
        }

        return $result;
    }
}
