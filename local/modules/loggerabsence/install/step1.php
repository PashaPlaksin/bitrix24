<?php

use Logger\Absence\Service\AbsenceService;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

IncludeModuleLangFile(__FILE__);

if (!check_bitrix_sessid()) return;

try {
    Loader::includeModule('iblock');
} catch (LoaderException $ex) {
    CAdminMessage::ShowMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => GetMessage('MOD_INST_ERR'),
        'DETAILS' => $ex->GetString(),
        'HTML' => true,
    ]);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/loggerabsence/lib/Service/AbsenceService.php';

$absenceService = new AbsenceService();
$iblockId = $absenceService->getIblockId();
$iblockIdDep = $absenceService->getIblockIdDep();
$absenceTypes = $absenceService->getAbsenceTypeEnumValues();
?>

<form method="POST">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="step" value="2">

    <label for="iblock_id"><?= GetMessage('IBLOCK') ?></label><br><br>
    <select name="iblock_id" id="iblock_id" required>
        <?php if ($iblockId): ?>
            <option value="<?= $iblockId ?>">
                [<?= $iblockId ?>] <?= htmlspecialcharsbx(GetMessage('TITLE_ABSENCE_IBLOCK')) ?>
            </option>
        <?php else: ?>
            <option disabled><?= GetMessage('IBLOCK_NOT_FOUND') ?></option>
        <?php endif; ?>
    </select><br><br>

    <label for="absence_type"><?= GetMessage('ABSENCE_TYPE') ?></label><br><br>
    <select name="absence_type" id="absence_type">
        <?php foreach ($absenceTypes as $id => $value): ?>
            <option value="<?= $id ?>"><?= htmlspecialcharsbx($value) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="iblock_dep_id"><?= GetMessage('IBLOCK_DEPARTMENT') ?></label><br><br>
    <select name="iblock_dep_id" id="iblock_dep_id" required>
        <?php if ($iblockIdDep): ?>
            <option value="<?= $iblockIdDep ?>">
                [<?= $iblockIdDep ?>] <?= htmlspecialcharsbx(GetMessage('TITLE_DEPARTMENT_IBLOCK')) ?>
            </option>
        <?php else: ?>
            <option disabled><?= GetMessage('IBLOCK_NOT_FOUND') ?></option>
        <?php endif; ?>
    </select><br><br>

    <input type="submit" name="step_submitted" value="<?= GetMessage('NEXT') ?>">
</form>