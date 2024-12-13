<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class CurrencyRateComponent extends CBitrixComponent
{
    /**Проверка наличия модулей требуемых для работы компонента
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    protected function checkModules(): void
    {
        if (!Loader::includeModule('currency')) {
            throw new \Exception("Модуль валют не установлен.");
        }
    }

    /**
     * @param $currency
     * @return Result
     */
    protected function getCurrencyRate($currency): Result
    {
        $result = new Result();

        try {
            $currencyData = CurrencyTable::getList([
                'filter' => ['=CURRENCY' => $currency],
                'select' => ['CURRENCY', 'AMOUNT']
            ])->fetch();

            if (!$currencyData) {
                $result->addError(new Error("Информация о валюте не найдена."));
            } else {
                $result->setData([
                    "CURRENCY" => $currencyData['CURRENCY'],
                    "AMOUNT" => $currencyData['AMOUNT'],
                ]);
            }
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }

    /**
     * @return void
     */
    protected function prepareResult(): void
    {
        $currency = $this->arParams["CURRENCY"] ?: "USD";

        $rateResult = $this->getCurrencyRate($currency);

        if ($rateResult->isSuccess()) {
            $this->arResult = $rateResult->getData();
        } else {
            $this->arResult["ERRORS"] = $rateResult->getErrorMessages();
        }
    }

    /**
     * @return void
     */
    public function executeComponent(): void
    {
        try {
            $this->checkModules();
            $this->prepareResult();
            $this->IncludeComponentTemplate();
        } catch (\Exception $e) {
            ShowError($e->getMessage());
        }
    }
}
