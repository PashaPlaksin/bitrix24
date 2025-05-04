<?php

namespace Otus\CustomFields;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

/**
 * Class IntegerType
 * @package Bitrix\Main\UserField\Types
 */
class LinkField
{
    public static function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE'        => 'S', // тип поля
            'USER_TYPE'            => 'iblock_link', // код типа пользовательского свойства
            'DESCRIPTION'          => 'Ссылка для href', // название типа пользовательского свойства
            'GetPropertyFieldHtml' => array(self::class, 'GetPropertyFieldHtml'), // метод отображения свойства
            'GetSearchContent' => array(self::class, 'GetSearchContent'), // метод поиска
            'GetAdminListViewHTML' => array(self::class, 'GetAdminListViewHTML'),  // метод отображения значения в списке
            'GetPublicEditHTML' => array(self::class, 'GetPropertyFieldHtml'), // метод отображения значения в форме редактирования
            'GetPublicViewHTML' => array(self::class, 'GetPublicViewHTML'), // метод отображения значения
        );
    }


    public static function PrepareSettings($arFields)
    {
        if(is_array($arFields["USER_TYPE_SETTINGS"]) && $arFields["USER_TYPE_SETTINGS"]["_BLANK"] == "Y"){
            return array("_BLANK" =>  "Y");
        }else{
            return array("_BLANK" =>  "N");
        }
    }


    public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName): string
    {
        $arSettings = self::PrepareSettings($arProperty);

        $arValues = array();
        if (!is_array($arProperty['VALUE'])) {
            $arProperty['VALUE'] = array($arProperty['VALUE']);
            $arProperty['DESCRIPTION'] = array($arProperty['DESCRIPTION']);
        }
        foreach ($arProperty['VALUE'] as $i => $value) {
            $arValues[$value] = $arProperty['DESCRIPTION'][$i];
        }

        $strResult = '';
        return '<a '.($arSettings["_BLANK"] == 'Y' ? 'target="_blank"'
                : '').' href="'.trim($arValue['VALUE']).'">'.(trim(
                $arValues[$arValue['VALUE']]
            ) ? trim($arValues[$arValue['VALUE']]) : trim($arValue['VALUE']))
            .'</a>';
    }


    public static function GetAdminListViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        $arSettings = self::PrepareSettings($arProperty);

        $strResult = '';
        return '<a '.($arSettings["_BLANK"] == 'Y' ? 'target="_blank"'
                : '').' href="'.trim($arValue['VALUE']).'">'.(trim(
                $arValue['DESCRIPTION']
            ) ? trim($arValue['DESCRIPTION']) : trim($arValue['VALUE'])).'</a>';
    }


    /**
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     * @return string
     */
    public static function GetSearchContent($arProperty, $value, $strHTMLControlName): string
    {
        if (trim($value['VALUE']) != '') {
            return $value['VALUE'] . ' ' . $value['DESCRIPTION'];
        }

        return '';
    }

    /**
     * @param $arProperty
     * @param $arValue
     * @param $strHTMLControlName
     * @return string
     */
    public static function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName): string
    {
        global $bVarsFromForm, $bCopy, $PROP, $APPLICATION;
        $strResult = '';

        $inpId = md5('link_' . rand(0, 999));

        if ($_REQUEST['mode'] == 'frame') {
            $strResult .= '<div><input type="text" name="' . $strHTMLControlName["VALUE"] . '" size="10" value="' . htmlspecialchars($arValue["VALUE"]) . '" />';
            if ($arProperty['WITH_DESCRIPTION'] == 'Y') {
                $strResult .= '&nbsp;<input type="text" name="' . $strHTMLControlName["DESCRIPTION"] . '" size="8" value="' . htmlspecialchars($arValue["DESCRIPTION"]) . '" /></div>';
            }
        } else {
            $strResult .= '<select id="pro' . $inpId . '">';
            $strResult .= '<option value="http://">http://</option>';
            $strResult .= '<option value="https://">https://</option>';
            $strResult .= '<option value="/">/ (local from root)</option>';
            $strResult .= '<option value="./">./ (local from current)</option>';
            $strResult .= '<option value="../">../ (local from parent)</option>';
            $strResult .= '</select>&nbsp;';
            $strResult .= '<input id="link' . $inpId . '" value="' . htmlspecialcharsex($arValue['VALUE']) . '" size="15" type="text">';
            $strResult .= '<input id="full' . $inpId . '" type="hidden" name="' . $strHTMLControlName["VALUE"] . '" value="' . htmlspecialcharsex($arValue['VALUE']) . '">';
            if ($arProperty['WITH_DESCRIPTION'] == 'Y') {
                $strResult .= ' <span>Текст ссылки: <input name="' . $strHTMLControlName["DESCRIPTION"] . '" value="' . htmlspecialcharsex($arValue['DESCRIPTION']) . '" size="15" type="text"></span>';
            }
            $strResult .= "<br>";

            $strResult .= '
        <script type="text/javascript">
            BX.bind(
                BX("link' . $inpId . '"),
                "bxchange",
                function()
                {
                    var linkval = BX("link' . $inpId . '").value;
                    linkval = linkval.trim();
                    var linkval_ = linkval.toLowerCase();
                    if (linkval_.substr(0, 8) == "https://")
                    {
                        BX("pro' . $inpId . '").value = "https://";
                        linkval = linkval.substr(8);
                        while (linkval.substr(0, 1) == "/")
                        {
                            linkval = linkval.substr(1);
                        }
                        BX("link' . $inpId . '").value = linkval;
                        BX("full' . $inpId . '").value = "https://" + linkval;
                    }
                    else if (linkval_.substr(0, 7) == "http://")
                    {
                        BX("pro' . $inpId . '").value = "http://";
                        linkval = linkval.substr(7);
                        while (linkval.substr(0, 1) == "/")
                        {
                            linkval = linkval.substr(1);
                        }
                        BX("link' . $inpId . '").value = linkval;
                        BX("full' . $inpId . '").value = "http://" + linkval;
                    }
                    else if (linkval_.substr(0, 2) == "//")
                    {
                        BX("pro' . $inpId . '").value = "http://";
                        while (linkval.substr(0, 1) == "/")
                        {
                            linkval = linkval.substr(1);
                        }
                        BX("link' . $inpId . '").value = linkval;
                        BX("full' . $inpId . '").value = "http://" + linkval;
                    }
                    else if (linkval_.substr(0, 1) == "/")
                    {
                        BX("pro' . $inpId . '").value = "/";
                        while (linkval.substr(0, 1) == "/")
                        {
                            linkval = linkval.substr(1);
                        }
                        BX("link' . $inpId . '").value = linkval;
                        BX("full' . $inpId . '").value = "/" + linkval;
                    }
                    else if (linkval_.substr(0, 2) == "./")
                    {
                        BX("pro' . $inpId . '").value = "./";
                        while (linkval.substr(0, 2) == "./")
                        {
                            linkval = linkval.substr(2);
                        }
                        BX("link' . $inpId . '").value = linkval;
                        BX("full' . $inpId . '").value = "./" + linkval;
                    }
                    else if (linkval_.substr(0, 3) == "../")
                    {
                        BX("pro' . $inpId . '").value = "../";
                        while (linkval.substr(0, 3) == "../")
                        {
                            linkval = linkval.substr(3);
                        }
                        BX("link' . $inpId . '").value = linkval;
                        BX("full' . $inpId . '").value = "../" + linkval;
                    }
                    else if (linkval.length > 0)
                    {
                        BX("full' . $inpId . '").value = BX("pro' . $inpId . '").value + linkval;
                    }
                    else
                    {
                        BX("full' . $inpId . '").value = "";
                    }
                }
            );
            
            BX.bind(
                BX("pro' . $inpId . '"),
                "bxchange",
                function()
                {
                    var protocol_ = BX("pro' . $inpId . '").value;
                    var linkval = BX("link' . $inpId . '").value;
                    linkval = linkval.trim();
                    while (linkval.substr(0, 1) == "/")
                    {
                        linkval = linkval.substr(1);
                    }
                    var linkval_ = linkval.toLowerCase();
                    if (linkval_.substr(0, 8) == "https://")
                    {
                        BX("link' . $inpId . '").value = linkval.substr(8);
                        BX("full' . $inpId . '").value = protocol_ + linkval.substr(8);
                    }
                    else if (linkval_.substr(0, 7) == "http://")
                    {
                        BX("link' . $inpId . '").value = linkval.substr(7);
                        BX("full' . $inpId . '").value = protocol_ + linkval.substr(7);
                    }
                    else if (linkval_.substr(0, 2) == "./")
                    {
                        BX("link' . $inpId . '").value = linkval.substr(2);
                        BX("full' . $inpId . '").value = protocol_ + linkval.substr(2);
                    }
                    else if (linkval_.substr(0, 3) == "../")
                    {
                        BX("link' . $inpId . '").value = linkval.substr(3);
                        BX("full' . $inpId . '").value = protocol_ + linkval.substr(3);
                    }
                    else if (linkval.length > 0)
                    {
                        BX("full' . $inpId . '").value = protocol_ + linkval;
                    }
                    else
                    {
                        BX("full' . $inpId . '").value = "";
                    }
                }
            );
            
            var linkval = BX("link' . $inpId . '").value;
            linkval = linkval.trim();
            var linkval_ = linkval.toLowerCase();
            if (linkval_.substr(0, 8) == "https://")
            {
                BX("pro' . $inpId . '").value = "https://";
                linkval = linkval.substr(8);
                while (linkval.substr(0, 1) == "/")
                {
                    linkval = linkval.substr(1);
                }
                BX("link' . $inpId . '").value = linkval;
                BX("full' . $inpId . '").value = "https://" + linkval;
            }
            else if (linkval_.substr(0, 7) == "http://")
            {
                BX("pro' . $inpId . '").value = "http://";
                linkval = linkval.substr(7);
                while (linkval.substr(0, 1) == "/")
                {
                    linkval = linkval.substr(1);
                }
                BX("link' . $inpId . '").value = linkval;
                BX("full' . $inpId . '").value = "http://" + linkval;
            }
            else if (linkval_.substr(0, 2) == "//")
            {
                BX("pro' . $inpId . '").value = "http://";
                while (linkval.substr(0, 1) == "/")
                {
                    linkval = linkval.substr(1);
                }
                BX("link' . $inpId . '").value = linkval;
                BX("full' . $inpId . '").value = "http://" + linkval;
            }
            else if (linkval_.substr(0, 1) == "/")
            {
                BX("pro' . $inpId . '").value = "/";
                while (linkval.substr(0, 1) == "/")
                {
                    linkval = linkval.substr(1);
                }
                BX("link' . $inpId . '").value = linkval;
                BX("full' . $inpId . '").value = "/" + linkval;
            }
            else if (linkval_.substr(0, 2) == "./")
            {
                BX("pro' . $inpId . '").value = "./";
                while (linkval.substr(0, 2) == "./")
                {
                    linkval = linkval.substr(2);
                }
                BX("link' . $inpId . '").value = linkval;
                BX("full' . $inpId . '").value = "./" + linkval;
            }
            else if (linkval_.substr(0, 3) == "../")
            {
                BX("pro' . $inpId . '").value = "../";
                while (linkval.substr(0, 3) == "../")
                {
                    linkval = linkval.substr(3);
                }
                BX("link' . $inpId . '").value = linkval;
                BX("full' . $inpId . '").value = "../" + linkval;
            }
            else if (linkval.length > 0)
            {
                BX("full' . $inpId . '").value = BX("pro' . $inpId . '").value + linkval;
            }
            else
            {
                BX("full' . $inpId . '").value = "";
            }
        </script>
            ';
        }

        return $strResult;
    }



}
