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
class UserField extends BaseType
{
    public const
        USER_TYPE_ID = 'custom',
        RENDER_COMPONENT = 'bitrix:main.field.integer';

    /**
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => 'Кастомный тип',
            'BASE_TYPE' => CUserTypeManager::BASE_TYPE_INT
        ];
    }

    /**
     * @return string
     */
    public static function getDbColumnType(): string
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $helper = $connection->getSqlHelper();
        return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\IntegerField('x'));
    }

    /**
     * @param array $userField
     * @return array
     */
    public static function prepareSettings(array $userField): array
    {
        $size = (int)($userField['SETTINGS']['SIZE'] ?? 0);
        $min = (int)($userField['SETTINGS']['MIN_VALUE'] ?? 0);
        $max = (int)($userField['SETTINGS']['MAX_VALUE'] ?? 0);
        $default = ($userField['SETTINGS']['DEFAULT_VALUE'] ?? '') !== ''
            ? (int)$userField['SETTINGS']['DEFAULT_VALUE']
            : null
        ;
        $custom = (int)($userField['SETTINGS']['CUSTOM'] ?? 0);
        return [
            'SIZE' => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
            'MIN_VALUE' => $min,
            'MAX_VALUE' => $max,
            'DEFAULT_VALUE' => $default,
            'CUSTOM' => $custom
        ];
    }

    public static function getSettingsHtml($userField, ?array $additionalParameters, $varsFromForm): string
    {

        $result = static::renderSettings(
            $userField,
            $additionalParameters,
            $varsFromForm
        );
        $result .= '<tr>
	<td class="adm-detail-content-cell-l">Кастомный параметр:</td>
	<td class="adm-detail-content-cell-r">
		<input type="text" name="'.$additionalParameters['NAME'].'[CUSTOM]" size="20" maxlength="225" value="'.$userField[$additionalParameters['NAME']]['CUSTOM'].'">
	</td>
</tr>';
         return $result;
    }

}
