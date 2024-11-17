<?php
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php';
/*
 * @var Cmain  $APPLICATION
 */

$APPLICATION -> setTitle("Отладка и логирование. ДЗ - 1");
\Bitrix\Main\Diag\Debug::writeToFile(date("d.m.Y H:i:s"),'',"/logs/1.txt");

echo ("Записали в файл /logs/1.txt текущую дату и время");
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php';