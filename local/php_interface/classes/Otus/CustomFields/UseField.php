<?php
namespace Otus\CustomFields;
/**
 * Class CUserTypeInteger
 * Type for custom properties - STRING
 *
 * @package usertype
 * @subpackage classes
 * @deprecated deprecated since main 20.0.700
 */
class UseField
{
    const USER_TYPE_ID =  'color';

    public static function getUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => 'color',
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => 'Выбор цвета',
            "BASE_TYPE" => \CUserTypeManager::BASE_TYPE_INT,
        );
    }


}
