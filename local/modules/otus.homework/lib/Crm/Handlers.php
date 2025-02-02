<?php

namespace Otus\Homework\Crm;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Handlers
{
    public static function updateTabs(Event $event): EventResult
    {
        $tabs = $event->getParameter('tabs');
        $entityTypeId = $event->getParameter('entityTypeID');
        if ($entityTypeId != \CCrmOwnerType::Deal && $entityTypeId != \CCrmOwnerType::Lead && $entityTypeId != \CCrmOwnerType::Contact && $entityTypeId != \CCrmOwnerType::Company) {
            return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, [
                'tabs' => $tabs,
            ]);
        }
        $entityId = $event->getParameter('entityID');

        $tabs[] = [
            'id' => 'otus_homework',
            'name' => 'Данные из Таблицы',
            'loader' => [
                'serviceUrl' => '/bitrix/components/otus.homework/otus.grid/lazyload.ajax.php?&site=' . \SITE_ID . '&' . \bitrix_sessid_get(),
                'componentData' => [
                    'template' => '',
                    'params' => [
                        'entityID' => $entityId,
                        'entityTypeID' => $entityTypeId,
                    ],
                ],
            ],
        ];
        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }
}
