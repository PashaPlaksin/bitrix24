<?php

namespace Otus\CustomRestHandler;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Bitrix\Main\LoaderException;
use Bitrix\Rest\RestException;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::includeModule('rest');
Loader::includeModule('main');

class CustomRestHandler
{
    /**
     * @return void
     * @throws LoaderException
     */
    public static function registerEvents(): void
    {
        if (!Loader::includeModule('rest') || !Loader::includeModule('main')) {
            return;
        }
        $eventManager = EventManager::getInstance();
        $eventManager->addEventHandlerCompatible('rest', 'OnRestServiceBuildDescription', [__CLASS__, 'onRestServiceBuildDescriptionHandler']);

    }

    /**
     * @return array[]
     */
    public static function onRestServiceBuildDescriptionHandler(): array
    {
        Loc::getMessage('REST_SCOPE_CUSTOM.ENTITY');

        return [
            'custom.entity' => [
                'custom.entity.add' => [
                    'callback' => [__CLASS__, 'addEntity'],
                    'options' => ['description' => Loc::getMessage('CUSTOM_ENTITY_ADD_DESC')]
                ],
                'custom.entity.get' => [
                    'callback' => [__CLASS__, 'getEntity'],
                    'options' => ['description' => Loc::getMessage('CUSTOM_ENTITY_GET_DESC')]
                ],
                'custom.entity.list' => [
                    'callback' => [__CLASS__, 'getList'],
                    'options' => ['description' => Loc::getMessage('CUSTOM_ENTITY_LIST_DESC')]
                ],
                'custom.entity.update' => [
                    'callback' => [__CLASS__, 'updateEntity'],
                    'options' => ['description' => Loc::getMessage('CUSTOM_ENTITY_UPDATE_DESC')]
                ],
                'custom.entity.delete' => [
                    'callback' => [__CLASS__, 'deleteEntity'],
                    'options' => ['description' => Loc::getMessage('CUSTOM_ENTITY_DELETE_DESC')]
                ],
            ]
        ];
    }

    /**Добавить запись в таблицу
     * @param $params
     * @param $navStart
     * @param \CRestServer $server
     * @return array
     * @throws RestException
     */
    public static function addEntity($params, $navStart, \CRestServer $server): array
    {
        $request = Context::getCurrent()->getRequest();
        self::logRequest($request);
        CustomEntityTable::validateAdd($params);
        $result = CustomEntityTable::add([
            'NAME' => $params['NAME'],
            'DESCRIPTION' => $params['DESCRIPTION'],
        ]);
        if (!$result->isSuccess()) {
            throw new RestException(json_encode($result->getErrorMessages(), JSON_UNESCAPED_UNICODE), RestException::ERROR_ARGUMENT, \CRestServer::STATUS_OK);
        }

        return ['ID' => $result->getId()];
    }

    /**Получить запись из таблицы по его ID
     * @param $params
     * @param $navStart
     * @param \CRestServer $server
     * @return mixed
     * @throws RestException
     */
    public static function getEntity($params, $navStart, \CRestServer $server): mixed
    {
        $request = Context::getCurrent()->getRequest();
        self::logRequest($request);
        CustomEntityTable::validateGet($params);
        $entity = CustomEntityTable::getById($params['ID'])->fetch();
        if (!$entity) {
            throw new RestException(Loc::getMessage('CUSTOM_ENTITY_GET_ERROR', ["#ID#" => $params['ID']]), 0);

        }
        return $entity;
    }

    /**Получить записи таблицы по фильтру
     * @param $params
     * @param $navStart
     * @param $server
     * @return array
     * @throws RestException
     */
    public static function getList($params, $navStart, $server): array
    {
        $request = Context::getCurrent()->getRequest();
        self::logRequest($request);
        $result = CustomEntityTable::getList([
            'select' => $params['SELECT'] ?: ['*'],
            'filter' => $params['FILTER'] ?? [],
            'order' => $params['ORDER'] ? [$params['order']['by'] => $params['order']['direction']] : ['ID' => 'ASC'],
            'limit' => $params['LIMIT'] ?? 50,
            'offset' => $navStart ?: 0,
        ]);

        $entities = [];
        while ($row = $result->fetch()) {
            $entities[] = $row;
        }

        return $entities;
    }

    /**Обновление записи таблицы
     * @param $params
     * @param $navStart
     * @param \CRestServer $server
     * @return array
     * @throws RestException
     */
    public static function updateEntity($params, $navStart, \CRestServer $server): array
    {
        $request = Context::getCurrent()->getRequest();
        self::logRequest($request);
        CustomEntityTable::validateUpdate($params);
        $result = CustomEntityTable::update($params['ID'], [
            'NAME' => $params['NAME'],
            'DESCRIPTION' => $params['DESCRIPTION'],
        ]);

        if (!$result->isSuccess()) {
            throw new RestException(json_encode($result->getErrorMessages(), JSON_UNESCAPED_UNICODE), RestException::ERROR_ARGUMENT, \CRestServer::STATUS_OK);
        }

        return ['ID' => $params['ID']];
    }

    /**Удаление записи таблицы по ID
     * @param $params
     * @param $navStart
     * @param \CRestServer $server
     * @return array
     * @throws RestException
     */
    public static function deleteEntity($params, $navStart, \CRestServer $server): array
    {
        $request = Context::getCurrent()->getRequest();
        self::logRequest($request);
        CustomEntityTable::validateDelete($params);
        $result = CustomEntityTable::delete($params['ID']);
        if (!$result->isSuccess()) {
            throw new RestException(json_encode($result->getErrorMessages(), JSON_UNESCAPED_UNICODE), RestException::ERROR_ARGUMENT, \CRestServer::STATUS_OK);
        }
        return ['ID' => $params['ID']];
    }

    /**Логирование запроса
     * @param $request
     * @return void
     */
    private static function logRequest($request): void
    {
        $logData = [
            'METHOD' => $request->getRequestMethod(),
            'URI' => $request->getRequestUri(),
            'PARAMS' => json_encode($request->getQueryList()->toArray()),
            'USER_AGENT' => $request->getHeader('User-Agent'),
            'TIME' => date('Y-m-d H:i:s'),
        ];
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/otus/rest_log.txt',  'REQUEST: ' . var_export($logData, true) . PHP_EOL, FILE_APPEND);

        //Debug::dumpToFile($logData, "",  $_SERVER['DOCUMENT_ROOT']."/otus/rest_log.txt"); - что-то не отрабатывает


    }
}

