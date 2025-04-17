<?php

namespace Logger\Absence\Integration\UI\EntitySelector;

use Bitrix\Intranet\CustomSection\Provider;
use Bitrix\Main\Web\Uri;

class CustomSectionProvider extends Provider
{
    // Определяет, доступна ли страница для пользователя
    public function isAvailable(string $pageSettings, int $userId): bool
    {
        // Можно реализовать доступ по ролям, но пока доступна всем
        return true;
    }

    // Возвращает параметры для подключения компонента на странице
    public function resolveComponent(string $pageSettings, Uri $url): ?Provider\Component
    {
        // Подключаем компонент
        $component = new Provider\Component();

        $component->setComponentName('absenceeventlogger:absence.grid');
        $component->setComponentTemplate('');
        $component->setComponentParams([]);

        return $component;
    }

    // ID счетчика для этой страницы, если нужно
    public function getCounterId(string $pageSettings): ?string
    {
        return null;
    }

    // Текущее значение счетчика
    public function getCounterValue(string $pageSettings): ?int
    {
        return null;
    }
}
