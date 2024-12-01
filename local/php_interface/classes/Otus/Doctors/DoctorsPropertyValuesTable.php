<?php

namespace Otus\Doctors;


use Otus\Doctors\AbstractIblockPropertyValuesTable;
use Bitrix\Main\Entity\ReferenceField;
class DoctorsPropertyValuesTable extends AbstractIblockPropertyValuesTable
{
    public const IBLOCK_ID = 16;

    public static function getMap(): array
    {
        $map = [
            'OKAZYVAEMYE_PROTSEDURY' => new ReferenceField(
                'OKAZYVAEMYE_PROTSEDURY',
                DoctorsProceduresPropertyValuesTable::class,
                ['=this.OKAZYVAEMYE_PROTSEDURY_ID' => 'ref.IBLOCK_ELEMENT_ID']
            )
        ];

        return parent::getMap() + $map;

    }

}