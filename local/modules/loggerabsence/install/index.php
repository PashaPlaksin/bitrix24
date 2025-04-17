<?php

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Highloadblock as HL;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionPageTable;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;


use Logger\Absence\AbsenceEventsHandlers;

class LoggerAbsence extends CModule
{
    var $MODULE_ID = 'loggerabsence';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "Y";
    public string $hlBlockName = "AbsenceEvents";
    public string $tableName = "hl_absence_events";

    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['ABSENCE_EVENT_LOGGER_VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['ABSENCE_EVENT_LOGGER_MODULE_VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('ABSENCE_EVENT_LOGGER_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('ABSENCE_EVENT_LOGGER_MODULE_DESC');
        $this->PARTNER_NAME = Loc::getMessage('ABSENCE_EVENT_LOGGER_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('ABSENCE_EVENT_LOGGER_PARTNER_URI');
    }

    /** Проверка версии ядра Битрикс
     *
     * @return null
     */
    public function isVersionD7()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '20.00.00');
    }

    /** Проверка версии PHP
     *
     * @return bool|int
     */
    public function phpVersion(): bool|int
    {
        return version_compare(PHP_VERSION, '8.0.0', '<');
    }


    /**
     * @throws LoaderException
     * @throws SystemException
     */
    function DoInstall()
    {
        global $APPLICATION, $USER, $step;
        if ($this->phpVersion()) {
            $APPLICATION->ThrowException(Loc::getMessage('ABSENCE_EVENT_LOGGER_INSTALL_ERROR_PHP'));
            return false;
        }

        if (!$this->isVersionD7()) {
            $APPLICATION->ThrowException(Loc::getMessage('ABSENCE_EVENT_LOGGER_INSTALL_ERROR_VERSION'));
        }
        if ($USER->IsAdmin()) {

            $step = (int)($_REQUEST['step'] ?? 1);

            if ($step == 1) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage('STEP1'), __DIR__ . "/step1.php");

            } elseif ($step == 2) {
                $iblockId = $_REQUEST['iblock_id'];
                $iblockIdDep = $_REQUEST['iblock_dep_id'];
                $absenceType = $_REQUEST['absence_type'];
                if ($this->createHLBlocks($iblockId)) {
                    $this->installSettings($iblockId, $absenceType, $iblockIdDep);
                    $this->registerEvents();
                    ModuleManager::registerModule($this->MODULE_ID);
                    $this->InstallFiles();
                    $this->addItemToMenu();
                }
                $APPLICATION->IncludeAdminFile(GetMessage('STEP2'), __DIR__ . "/step2.php");
            }

        }
    }

    /** Метод проверки наличия HL-блока, создает если отсутствует
     *
     * @param $iblockId
     * @return bool
     * @throws LoaderException
     * @throws SystemException
     */
    private function createHLBlocks($iblockId): bool
    {
        global $APPLICATION;

        if (!Loader::includeModule('highloadblock')) {
            $APPLICATION->ThrowException(Loc::getMessage('HL_BLOCK_MODULE_LOAD_ERROR'));
            return false;
        }

        $hlblock = HL\HighloadBlockTable::getList([
            'filter' => ['=NAME' => $this->hlBlockName],
        ])->fetch();

        if ($hlblock) {
            return true;
        }

        $result = HL\HighloadBlockTable::add([
            'NAME' => $this->hlBlockName,
            'TABLE_NAME' => $this->tableName,
        ]);

        if (!$result->isSuccess()) {
            $APPLICATION->ThrowException(Loc::getMessage('HL_BLOCK_ADD_ERROR') . implode(', ', $result->getErrorMessages()));
            return false;
        }
        $hlBlockId = $result->getId();
        $fields = [
            ['UF_CREATED_BY', 'employee', Loc::getMessage('EMPLOYEE_EVENTS'), Loc::getMessage('LINK_TO_EMPLOYEE'), false],
            ['UF_MODIFY_BY', 'employee', Loc::getMessage('OPERATION_INITIATOR'), Loc::getMessage('LINK_TO_EMPLOYEE'), false],
            ['UF_OPERATION_TYPE', 'enumeration', Loc::getMessage('OPERATION_TYPE'), Loc::getMessage('OPERATION_TYPE'), false],
            ['UF_SEARCHABLE_CONTENT', 'string', Loc::getMessage('REASON_FOR_ABSENCE'), Loc::getMessage('REASON_FOR_ABSENCE'), false],
            ['UF_EVENT_TYPE', 'enumeration', Loc::getMessage('ABSENCE_TYPE'), Loc::getMessage('ABSENCE_TYPE'), false],
            ['UF_ACTIVE_FROM', 'datetime', Loc::getMessage('START_ABSENCE_PERIOD'), Loc::getMessage('START_ABSENCE_PERIOD'), false],
            ['UF_ACTIVE_TO', 'datetime', Loc::getMessage('END_ABSENCE_PERIOD'), Loc::getMessage('END_ABSENCE_PERIOD'), false],
            ['UF_DEPARTMENT', 'integer', Loc::getMessage('DEPARTMENT'), Loc::getMessage('DEPARTMENT'), false],
            ['UF_DATE_CREATE', 'datetime', Loc::getMessage('EVENT_DATE'), Loc::getMessage('EVENT_DATE'), true],
        ];

        $entityId = 'HLBLOCK_' . $hlBlockId;
        $uf = new CUserTypeEntity;

        foreach ($fields as [$fieldName, $type, $xmlId, $label, $required]) {
            $field = [
                'ENTITY_ID' => $entityId,
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => $type,
                'XML_ID' => $xmlId,
                'SORT' => 100,
                'MANDATORY' => $required ? 'Y' : 'N',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_FORM_LABEL' => ['ru' => $label],
                'LIST_COLUMN_LABEL' => ['ru' => $label],
                'LIST_FILTER_LABEL' => ['ru' => $label],
                'SETTINGS' => [],
                'IS_SEARCHABLE' => 'Y',
            ];

            $fieldId = $uf->Add($field);

        }
        $enum = new CUserFieldEnum;

        // UF_EVENT_TYPE - заполняем список значений
        $eventTypeField = CUserTypeEntity::GetList([], [
            'ENTITY_ID' => $entityId,
            'FIELD_NAME' => 'UF_EVENT_TYPE',
        ])->Fetch();

        if ($eventTypeField && $eventTypeField['ID']) {

            Loader::includeModule('iblock');

            $property = PropertyTable::getList([
                'filter' => [
                    'IBLOCK_ID' => $iblockId,
                    'CODE' => 'ABSENCE_TYPE',
                    'PROPERTY_TYPE' => 'L'
                ],
                'select' => ['ID']
            ])->fetch();

            if (!$property) {
                $APPLICATION->ThrowException(Loc::getMessage('ERROR_ABSENCE_TYPE_PROPERTY'));
                return false;
            }

            $enumValuesRaw = PropertyEnumerationTable::getList([
                'filter' => ['PROPERTY_ID' => $property['ID']],
                'select' => ['XML_ID', 'VALUE', 'SORT'],
                'order' => ['SORT' => 'ASC']
            ])->fetchAll();

            // Подготавливаем массив ENUM значений для HL-блока из самого инфоблока ГО
            $enumValuesEventType = [];
            $i = 0;
            foreach ($enumValuesRaw as $enumArr) {
                $enumValuesEventType["n{$i}"] = [
                    'XML_ID' => $enumArr['XML_ID'],
                    'VALUE' => $enumArr['VALUE'],
                    'SORT' => $enumArr['SORT']
                ];
                $i++;
            }

            $enum->SetEnumValues($eventTypeField['ID'], $enumValuesEventType);
        }

        // UF_OPERATION_TYPE - - заполняем список значений
        $operationTypeField = CUserTypeEntity::GetList([], [
            'ENTITY_ID' => $entityId,
            'FIELD_NAME' => 'UF_OPERATION_TYPE',
        ])->Fetch();

        if ($operationTypeField && $operationTypeField['ID']) {
            $enumValuesOperationType = [
                'n0' => ['XML_ID' => 'ADD', 'VALUE' => Loc::getMessage('ADD')],
                'n1' => ['XML_ID' => 'UPDATE', 'VALUE' => Loc::getMessage('UPDATE')],
                'n2' => ['XML_ID' => 'DELETE', 'VALUE' => Loc::getMessage('DELETE')],
            ];
            $enum->SetEnumValues($operationTypeField['ID'], $enumValuesOperationType);
        }

        return true;
    }

    /** Метод сохраняет настройки модуля
     * @param $iblockId
     * @param $absenceType
     * @param $iblockIdDep
     * @return void
     */
    private function installSettings($iblockId, $absenceType, $iblockIdDep): void
    {
        Option::set($this->MODULE_ID, 'IBLOCK_ID', $iblockId);
        Option::set($this->MODULE_ID, 'ABSENCE_TYPE', $absenceType);
        Option::set($this->MODULE_ID, 'IBLOCK_ID_DEPARTMENT', $iblockIdDep);
    }

    /** Метод регистрации событий инфоблоков
     *
     * @return void
     */
    public function RegisterEvents(): void
    {
        EventManager::getInstance()->registerEventHandler(
            "iblock",
            "OnBeforeIBlockElementDelete",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onBeforeElementDelete"
        );

        EventManager::getInstance()->registerEventHandler(
            "iblock",
            "OnAfterIBlockElementAdd",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onAfterElementAdd"
        );

        EventManager::getInstance()->registerEventHandler(
            "iblock",
            "OnAfterIBlockElementUpdate",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onAfterElementUpdate"
        );

        EventManager::getInstance()->registerEventHandler(
            "iblock",
            "OnBeforeIBlockElementAdd",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onBeforeElementAddOrUpdate"
        );

        EventManager::getInstance()->registerEventHandler(
            "iblock",
            "OnBeforeIBlockElementUpdate",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onBeforeElementAddOrUpdate"
        );

    }

    /**
     *
     * @throws LoaderException
     */
    public function DoUninstall()
    {
        global $APPLICATION, $step, $USER;
        if ($USER->IsAdmin()) {
            $step = intval($step);
            if ($step < 2) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage('STEP1_UNINSTALL'), __DIR__ . "/unstep1.php");
            } elseif ($step == 2) {
                if ($this->deleteHLBlocks(['save_tables' => $_REQUEST['save_tables'],])) {
                    $this->unInstallSettings();
                    $this->unRegisterEvents();
                    ModuleManager::unRegisterModule($this->MODULE_ID);
                    $this->UnInstallFiles();
                    $this->deleteItemToMenu();
                }
                $APPLICATION->IncludeAdminFile(Loc::getMessage('STEP2_UNINSTALL'), __DIR__ . "/unstep2.php");
            }
        }
    }

    /** Метод удаления HL-блока
     *
     * @param array $arParams
     * @return bool
     * @throws LoaderException
     */
    private function deleteHLBlocks(array $arParams = []): bool
    {
        global $APPLICATION;

        Loader::includeModule('highloadblock');

        if (!array_key_exists('save_tables', $arParams) || $arParams['save_tables'] != 'Y') {

            $result = HL\HighloadBlockTable::getList([
                'filter' => ['NAME' => $this->hlBlockName],
                'select' => ['ID']
            ]);

            if ($highloadBlock = $result->fetch()) {
                $blockId = $highloadBlock['ID'];

                $deleteResult = HL\HighloadBlockTable::delete($blockId);

                if (!$deleteResult->isSuccess()) {
                    $APPLICATION->ThrowException(Loc::getMessage('HL_BLOCK_DELETE_ERROR'));
                    return false;
                }
            } else {
                $APPLICATION->ThrowException(Loc::getMessage('HL_BLOCK_NOT_FOUND'));
                return false;
            }
        }
        return true;
    }

    /** Метод отмены регистрации на события инфоблоков
     * @return void
     */
    public function unRegisterEvents(): void
    {

        EventManager::getInstance()->unRegisterEventHandler(
            "iblock",
            "OnBeforeIBlockElementDelete",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onBeforeElementDelete"
        );

        EventManager::getInstance()->unRegisterEventHandler(
            "iblock",
            "OnAfterIBlockElementAdd",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onAfterElementAdd"
        );

        EventManager::getInstance()->unRegisterEventHandler(
            "iblock",
            "OnAfterIBlockElementUpdate",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onAfterElementUpdate"
        );
        EventManager::getInstance()->unRegisterEventHandler(
            "iblock",
            "OnBeforeIBlockElementAdd",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onBeforeElementAddOrUpdate"
        );

        EventManager::getInstance()->unRegisterEventHandler(
            "iblock",
            "OnBeforeIBlockElementUpdate",
            $this->MODULE_ID,
            AbsenceEventsHandlers::class,
            "onBeforeElementAddOrUpdate"
        );

    }

    /** Метод удаляет настройки модуля
     *
     * @return void
     */
    private function unInstallSettings(): void
    {
        Option::delete($this->MODULE_ID);
    }

    public function GetPath($notDocumentRoot = false): array|string
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    /** Метод инсталляции файлов компонента
     *
     * @throws InvalidPathException
     */
    public function InstallFiles(): void
    {
        $component_path = $this->GetPath() . '/install/components';
        if (Directory::isDirectoryExists($component_path)) {
            CopyDirFiles($component_path, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
        } else {
            throw new InvalidPathException($component_path);
        }
    }

    /** Метод деинсталляция файлов компонента
     *
     * @return void
     */
    public function UnInstallFiles(): void
    {
        $component_path = $_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/' . $this->MODULE_ID;
        if (Directory::isDirectoryExists($component_path)) {
            Directory::deleteDirectory($component_path);
        }
    }

    /** Метод создает секцию и страницу главного меню публичного раздела для модуля
     *
     * @return void
     * @throws ArgumentException
     */
    private function addItemToMenu(): void
    {
        global $APPLICATION;
        $sectionCode = $this->MODULE_ID;
        $pageCode = $sectionCode . '_main';

        // Проверка, существует ли уже раздел
        $section = CustomSectionTable::query()
            ->addSelect('ID')
            ->where('CODE', $sectionCode)
            ->where('MODULE_ID', $this->MODULE_ID)
            ->fetch();

        if (!$section) {
            // Добавляем раздел
            $result = CustomSectionTable::add([
                'CODE' => $sectionCode,
                'TITLE' => Loc::getMessage('MENU_ITEM_TITLE'),
                'MODULE_ID' => $this->MODULE_ID,
            ]);

            if (!$result->isSuccess()) {
                $APPLICATION->ThrowException(Loc::getMessage('CUSTOM_SECTION_ERROR', ['#ERROR#' => implode(', ', $result->getErrorMessages())]));
                return;
            }

            $sectionId = $result->getId();
        } else {
            $sectionId = $section['ID'];
        }

        // Проверка, существует ли уже страница
        $page = CustomSectionPageTable::query()
            ->addSelect('ID')
            ->where('CODE', $pageCode)
            ->where('MODULE_ID', $this->MODULE_ID)
            ->fetch();

        if (!$page) {
            // Добавляем страницу
            $result = CustomSectionPageTable::add([
                'CUSTOM_SECTION_ID' => $sectionId,
                'CODE' => $pageCode,
                'TITLE' => Loc::getMessage('MENU_ITEM_TITLE'),
                'MODULE_ID' => $this->MODULE_ID,
                'SORT' => 100,
                'SETTINGS' => \Bitrix\Main\Web\Json::encode(['view' => 'page']),
            ]);

            if (!$result->isSuccess()) {
                $APPLICATION->ThrowException(Loc::getMessage('PAGE_ADD_ERROR', ['#ERROR#' => implode(', ', $result->getErrorMessages())]));
            }
        }
    }

    /** Метод удаляет секцию и страницу главного меню публичного раздела для модуля
     *
     * @return void
     */
    private function deleteItemToMenu(): void
    {        
        $sectionCode = $this->MODULE_ID;
        $pageCode = $sectionCode . '_main';

        $page = CustomSectionPageTable::query()
            ->addSelect('ID')
            ->where('CODE', $pageCode)
            ->where('MODULE_ID', $this->MODULE_ID)
            ->fetch();

        if ($page) {
            CustomSectionPageTable::delete($page['ID']);
        }

        $section = CustomSectionTable::query()
            ->addSelect('ID')
            ->where('CODE', $sectionCode)
            ->where('MODULE_ID', $this->MODULE_ID)
            ->fetch();

        if ($section) {
            CustomSectionTable::delete($section['ID']);
        }
    }
}
