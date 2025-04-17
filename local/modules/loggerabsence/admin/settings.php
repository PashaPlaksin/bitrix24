<?php
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$module_id = 'loggerabsence';

Loc::loadMessages(__FILE__);

if (!$USER->IsAdmin()) return;

$APPLICATION->SetTitle(Loc::getMessage("LOGGERABSENCE_OPTIONS_TITLE"));

Loader::includeModule($module_id);
Loader::includeModule('iblock');

$iblockId = Option::get($module_id, 'IBLOCK_ID');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $valueId = (int)$_POST['ABSENCE_TYPE'];
    Option::set($module_id, 'ABSENCE_TYPE', $valueId);
}

$currentValueId = Option::get($module_id, 'ABSENCE_TYPE');

$property = CIBlockProperty::GetList([], [
    "IBLOCK_ID" => $iblockId,
    "CODE" => "ABSENCE_TYPE"
])->Fetch();

$propertyValues = [];

if ($property) {
    //проверяется тип свойства инфоблока, L - список, Е - привязка к элементам другого инфоблока
    if ($property["PROPERTY_TYPE"] === "L") {
        $res = CIBlockPropertyEnum::GetList(["SORT" => "ASC"], ["PROPERTY_ID" => $property["ID"]]);
        while ($enum = $res->Fetch()) {
            $propertyValues[] = [
                "ID" => $enum["ID"],
                "VALUE" => $enum["VALUE"]
            ];
        }
    }
    elseif ($property["PROPERTY_TYPE"] === "E") {
        $res = CIBlockElement::GetList(
            ["SORT" => "ASC"],
            ["IBLOCK_ID" => $property["LINK_IBLOCK_ID"], "ACTIVE" => "Y"],
            false,
            false,
            ["ID", "NAME"]
        );
        while ($el = $res->Fetch()) {
            $propertyValues[] = [
                "ID" => $el["ID"],
                "VALUE" => $el["NAME"]
            ];
        }
    }
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form method="post">
    <?= bitrix_sessid_post() ?>
    <table class="adm-detail-content-table edit-table">
        <tr>
            <td width="40%"><?= Loc::getMessage("LOGGERABSENCE_OPTIONS_ABSENCE_TYPE") ?>:</td>
            <td width="40%">
                <?php if (!empty($propertyValues)): ?>
                    <select name="ABSENCE_TYPE">
                        <?php foreach ($propertyValues as $val): ?>
                            <option value="<?= $val['ID'] ?>" <?= ($val['ID'] == $currentValueId) ? 'selected' : '' ?>>
                                <?= htmlspecialcharsbx($val['VALUE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <span style="color: red;">
                        <?= Loc::getMessage("LOGGERABSENCE_OPTIONS_NO_VALUES") ?>
                    </span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <input type="submit" value="<?= Loc::getMessage("LOGGERABSENCE_OPTIONS_SAVE") ?>" class="adm-btn-save" />
</form>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
