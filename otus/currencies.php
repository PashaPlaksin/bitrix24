<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Курсы валют");

$APPLICATION->IncludeComponent(
    "otus:currency.rate",
    "",
    [
        "CURRENCY" => "USD", // Значение по умолчанию
    ]
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
