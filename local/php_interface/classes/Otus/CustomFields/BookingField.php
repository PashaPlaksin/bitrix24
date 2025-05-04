<?php

namespace Otus\CustomFields;

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use CIBlockPropertyElementList;
use CUserTypeManager;

/**
 * Класс для обработки поля "Бронирование".
 *
 * Class BookingField
 * @package Bitrix\Main\UserField\Types
 */
class BookingField extends CIBlockPropertyElementList
{
    /**
     * Описание пользовательского типа поля.
     *
     * @return array Описание типа поля.
     */
    public static function GetUserTypeDescription(): array
    {
        return [
            'PROPERTY_TYPE'         => 'E',
            'USER_TYPE'             => 'booking',
            'DESCRIPTION'           => 'Бронирование',
            'GetPropertyFieldHtml'  => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetPropertyFieldHtmlMulty' => [__CLASS__, 'GetPropertyFieldHtmlMulty'],
            'GetPublicEditHTML'     => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetPublicEditHTMLMulty' => [__CLASS__, 'GetPropertyFieldHtmlMulty'],
            'GetPublicViewHTML'     => [__CLASS__, 'GetPublicViewHTML'],
            'GetUIFilterProperty'   => [__CLASS__, 'GetUIFilterProperty'],
            'GetAdminFilterHTML'    => [__CLASS__, 'GetAdminFilterHTML'],
            'PrepareSettings'       => [__CLASS__, 'PrepareSettings'],
            'GetSettingsHTML'       => [__CLASS__, 'GetSettingsHTML'],
            'GetExtendedValue'      => [__CLASS__, 'GetExtendedValue'],
            'GetUIEntityEditorProperty' => [__CLASS__, 'GetUIEntityEditorProperty'],
        ];
    }

    /**
     * Генерирует HTML для отображения поля на публичной странице.
     *
     * @param array $arProperty Массив с параметрами свойства.
     * @param array $arValue Массив с значениями для поля.
     * @param array $strHTMLControlName Массив с параметрами для HTML элемента.
     * @return string HTML код для отображения поля.
     */
    public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName): string
    {
        $procedureId = $arValue['VALUE'];
        $elementId = $arProperty['ELEMENT_ID'];

        $id = md5($arProperty['FIELD_ID'] . $procedureId . $arProperty['ELEMENT_ID'] . $strHTMLControlName['VALUE']);
        $str = parent::GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName);
        $str = str_replace("<a", "<a id='link-$id' data-procedure='$procedureId' data-element-id='$elementId'", $str);

        ob_start();
        ?>
        <div id="popup-<?= $id ?>" style="display:none; background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <h1 style="color: #333;">Процедура</h1>
            <form method="POST" action="">
                <div class="ui-ctl ui-ctl-textbox" style="margin-top: 20px;">
                    <input name="name" type="text" class="ui-ctl-element" placeholder="ФИО пациента" style="padding: 10px;">
                </div>
                <div class="ui-ctl ui-ctl-textbox" style="margin-top: 20px; margin-left: 0;">
                    <input onclick="BX.calendar({node: this, field: this, bTime: true});" name="time" type="text" class="ui-ctl-element" placeholder="Время записи" style="padding: 10px;">
                </div>
                <input class="ui-ctl-element" type="hidden" value="" name="procedure">
                <input class="ui-ctl-element" type="hidden" value="" name="element_id">
            </form>
        </div>
        <script>
            BX.ready(function () {
                let link = BX("link-<?=$id?>");
                BX.bind(link, 'click', function(event) {
                    let popup = BX.PopupWindowManager.create("booking-popup<?=$id?>", null, {
                        content: BX('popup-<?=$id?>'),
                        className: 'bx-filter-select-popup-window',
                        autoHide: true,
                        offsetLeft: 0,
                        offsetTop: 8,
                        overlay: false,
                        draggable: false,
                        closeByEsc: true,
                        closeIcon: {right: "10px", top: "15px"},
                        width: 550,
                        maxHeight: 550,
                        titleBar: "Запись на процедуру",
                        buttons: [
                            new BX.PopupWindowButton({
                                text: 'Записать',
                                id: 'save-btn',
                                className: 'ui-btn ui-btn-success',
                                events: {
                                    click: function() {
                                        BX.remove(BX('alert'));
                                        let formData = new FormData();
                                        let inputs = BX.findChild(BX('popup-window-content-booking-popup<?=$id?>'), {
                                            tag: 'input',
                                            props: {className: 'ui-ctl-element'}
                                        }, true, true);

                                        let allValid = true;
                                        inputs.forEach((element) => {
                                            BX.bind(element, 'focus', function() {
                                                BX.remove(BX('alert'));
                                                let parent = BX.findParent(element, {className : 'ui-ctl'});
                                                BX.removeClass(parent, 'ui-ctl-warning');
                                            });

                                            if (!element.value) {
                                                allValid = false;
                                                let parent = BX.findParent(element, {className : 'ui-ctl'});
                                                BX.addClass(parent, 'ui-ctl-warning');
                                            }

                                            formData.append(element.getAttribute('name'), element.value);
                                        });

                                        if (!allValid) {
                                            let alert = BX.create({
                                                tag: 'div',
                                                props: {
                                                    className: "ui-alert ui-alert-danger",
                                                    id: 'alert'
                                                },
                                                children: [
                                                    BX.create({
                                                        tag: 'span',
                                                        props: {className: 'ui-alert-message'},
                                                        text: 'Не заполнены обязательные поля'
                                                    }),
                                                ]
                                            });
                                            BX.prepend(alert, BX("popup-window-content-booking-popup<?=$id?>"));
                                            return;
                                        }

                                        formData.append('sessid', BX.bitrix_sessid());

                                        BX.ajax({
                                            dataType: 'json',
                                            processData: false,
                                            preparePost: false,
                                            url: '/local/ajax/ajax.php',
                                            method: 'POST',
                                            data: formData,
                                            onsuccess: function (data) {
                                                let result = JSON.parse(data);
                                                let alertClass = result.error ? "ui-alert-danger" : "ui-alert-success";
                                                let alertText = result.error || result.result;

                                                let alert = BX.create({
                                                    tag: 'div',
                                                    props: {
                                                        className: "ui-alert " + alertClass,
                                                        id: 'alert'
                                                    },
                                                    children: [
                                                        BX.create({
                                                            tag: 'span',
                                                            props: {className: 'ui-alert-message'},
                                                            text: alertText
                                                        }),
                                                    ]
                                                });
                                                BX.prepend(alert, BX("popup-window-content-booking-popup<?=$id?>"));
                                            },
                                            onfailure: function () {
                                                let alert = BX.create({
                                                    tag: 'div',
                                                    props: {
                                                        className: "ui-alert ui-alert-danger",
                                                        id: 'alert'
                                                    },
                                                    children: [
                                                        BX.create({
                                                            tag: 'span',
                                                            props: {className: 'ui-alert-message'},
                                                            text: "Ошибка"
                                                        }),
                                                    ]
                                                });
                                                BX.prepend(alert, BX("popup-window-content-booking-popup<?=$id?>"));
                                            }
                                        });
                                    }
                                }
                            }),
                            new BX.PopupWindowButton({
                                text: 'Закрыть',
                                id: 'copy-btn',
                                className: 'ui-btn ui-btn-primary',
                                events: {
                                    click: function() {
                                        this.popupWindow.close();
                                    }
                                }
                            })
                        ],
                        events: {
                            onPopupShow: () => {
                                let popupContent = BX("popup-window-content-booking-popup<?=$id?>");

                                let procedureInput = popupContent.querySelector('input[name="procedure"]');
                                let elementIdInput = popupContent.querySelector('input[name="element_id"]');

                                let procedureId = link.getAttribute('data-procedure');
                                let elementId = link.getAttribute('data-element-id');
                                let procedureTitle = link.text;

                                procedureInput.value = procedureId;
                                elementIdInput.value = elementId;

                                let h1 = popupContent.querySelector('h1');
                                h1.textContent = procedureTitle;
                            }
                        }
                    });

                    event.preventDefault();
                    popup.show();
                });
            });
        </script>
        <?php
        return $str . ob_get_clean();
    }
}
