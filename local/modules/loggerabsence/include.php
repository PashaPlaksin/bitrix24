<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

const LO_MODULE_ID = 'loggerabsence';

\Bitrix\Main\Loader::registerAutoLoadClasses(LO_MODULE_ID, [

    '\Logger\Absence\AbsenceEventsHandlers' => 'lib/AbsenceEventsHandlers.php',
    '\Logger\Absence\AbsenceEventLogger' => 'lib/AbsenceEventLogger.php',

    '\Logger\Absence\Integration\UI\EntitySelector\CustomSectionProvider' => 'lib/integration/ui/entityselector/customsectionprovider.php',
    '\Logger\Absence\Service\AbsenceService' => 'lib/Service/AbsenceService.php',
    '\Logger\Absence\Service\AbsenceLogTable' => 'lib/Service/AbsenceLogTable.php',
]);
