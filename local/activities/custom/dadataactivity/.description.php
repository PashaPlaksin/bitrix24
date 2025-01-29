<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("DADATA_NAME"),
    "DESCRIPTION" => Loc::getMessage("DADATA_DESCRIPTION"),
    "TYPE" => "activity",
    "CLASS" => "DadataActivity",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "COMPANY_FULL_NAME" => [
            "NAME" => Loc::getMessage("DADATA_FIELD_COMPANY_FULL"),
            "TYPE" => "string",
        ],
        "COMPANY_SHORT_NAME" => [
            "NAME" => Loc::getMessage("DADATA_FIELD_COMPANY_SHORT"),
            "TYPE" => "string",
        ],
        "COMPANY_ADDRESS" => [
            "NAME" => Loc::getMessage("DADATA_FIELD_ADDRESS"),
            "TYPE" => "string",
        ],
        "KPP" => [
            "NAME" => Loc::getMessage("DADATA_FIELD_KPP"),
            "TYPE" => "string",
        ],
        "RESPONSE" => [
            "NAME" => Loc::getMessage("DADATA_FIELD_RESPONSE"),
            "TYPE" => "string",
        ],
        "INN" => [
            "NAME" => Loc::getMessage("DADATA_FIELD_INN"),
            "TYPE" => "string",
        ],
    ],
];