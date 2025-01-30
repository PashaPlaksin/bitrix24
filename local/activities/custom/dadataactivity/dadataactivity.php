<?php
use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\PropertiesDialog;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CBPDadataActivity extends BaseActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            "INN" => "",
            "COMPANY_FULL_NAME" => "",
            "COMPANY_SHORT_NAME" => "",
            "COMPANY_ADDRESS" => "",
            "KPP" => "",
            "RESPONSE" => ""
        ];
    }
    /**
     * Return activity file path
     * @return string
     */
    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * @return int
     */
    public function Execute(): int
    {
        $errors = parent::internalExecute();

        $inn = $this->INN;
        $apiKey = "КЛЮЧ_API";

        if (!$inn) {
            $this->arProperties["RESPONSE"] = Loc::getMessage('DADATA_ACTIVITY_ERROR_INN');
            return CBPActivityExecutionStatus::Closed;
        }

       $url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";
        $headers = [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Token $apiKey"
        ];
        $data = json_encode(["query" => $inn]);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $this->arProperties["RESPONSE"] = Loc::getMessage('DADATA_ACTIVITY_ERROR_DADATA');
            return CBPActivityExecutionStatus::Closed;
        }

        $responseData = json_decode($response, true);
        if (empty($responseData["suggestions"])) {
            $this->arProperties["RESPONSE"] = Loc::getMessage('DADATA_ACTIVITY_ERROR_COMPANY');
            return CBPActivityExecutionStatus::Closed;
        }

        $company = $responseData["suggestions"][0]["data"];
        $this->arProperties["INN"] = $company["inn"] ?? ""; // или $this->INN
        $this->arProperties["COMPANY_FULL_NAME"] = $company["name"]["full_with_opf"] ?? "";
        $this->arProperties["COMPANY_SHORT_NAME"] = $company["name"]["short_with_opf"] ?? "";
        $this->arProperties["COMPANY_ADDRESS"] = $company["address"]["value"] ?? "";
        $this->arProperties["KPP"] = $company["kpp"] ?? "";

        return CBPActivityExecutionStatus::Closed;
    }

    /**
     * @param PropertiesDialog|null $dialog
     * @return array[]
     */
    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        $map = [
            'INN' => [
                'Name' => Loc::getMessage('DADATA_ACTIVITY_FIELD_INN_SUBJECT'),
                'FieldName' => 'INN',
                'Type' => FieldType::STRING,
                'Required' => true,
                'Options' => [],
            ],
        ];
        return $map;
    }
}
