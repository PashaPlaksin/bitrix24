<?php
namespace Otus\Homework;

use Bitrix\Main\Entity;
class OtusEntityTable extends Entity\DataManager
{
    public static function getTableName(): string
    {
        return 'otus_homework';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\IntegerField('CRM_ENTITY_ID', ['required' => true]),
            new Entity\IntegerField('CRM_ENTITY_TYPE', ['required' => true]),
            new Entity\StringField('NAME', ['required' => true]),
            new Entity\TextField('VALUE'),
        ];
    }
}
