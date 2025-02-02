<?php

use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

class otus_homework extends CModule
{
    public $MODULE_ID = 'otus.homework';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['OTUS_MODULE_VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['OTUS_MODULE_VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('OTUS_HOMEWORK_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('OTUS_HOMEWORK_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('OTUS_HOMEWORK_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('OTUS_HOMEWORK_PARTNER_URI');
    }

    public function isVersionD7()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '20.00.00');
    }

    public function GetPath($notDocumentRoot = false): array|string
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    public function DoInstall(): void
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {
            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallDB();
            $this->installFiles();
            $this->InstallEvents();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('OTUS_HOMEWORK_INSTALL_ERROR_VERSION'));
        }
    }

    public function DoUninstall(): void
    {
        $this->UnInstallDB();
        $this->unInstallFiles();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installFiles(): void
    {
        $component_path = $this->GetPath() . '/install/components';
        if (Directory::isDirectoryExists($component_path)) {
            CopyDirFiles($component_path, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
        } else {
            throw new InvalidPathException($component_path);
        }
    }

    public function unInstallFiles()
    {
        $component_path = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/" . $this->MODULE_ID;
        print_r($component_path);
        if (Directory::isDirectoryExists($component_path)) {
            Directory::deleteDirectory($component_path);
        }
    }

    public function InstallDB(): void
    {
        global $DB;
        $DB->RunSQLBatch(__DIR__ . "/db/install.sql");
    }

    public function UnInstallDB(): void
    {
        global $DB;
        $DB->RunSQLBatch(__DIR__ . "/db/uninstall.sql");
    }

    public function InstallEvents(): bool
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Homework\\Crm\\Handlers',
            'updateTabs'
        );

        return true;
    }

    public function UnInstallEvents(): bool
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Homework\\Crm\\Handlers',
            'updateTabs'
        );

        return true;
    }
}
