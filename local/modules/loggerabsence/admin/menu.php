<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return [
    [
        "parent_menu" => "global_menu_settings",
        "section" => "loggerabsence",
        "sort" => 100,
        "text" => Loc::getMessage("LOGGERABSENCE_MENU_TEXT"),
        "title" => Loc::getMessage("LOGGERABSENCE_MENU_TITLE"),
        "url" => "loggerabsence_settings.php?lang=" . LANGUAGE_ID,
        "items_id" => "menu_loggerabsence",
        "icon" => "util_menu_icon",
    ],
];
