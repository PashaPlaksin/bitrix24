<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="currency-rate">
	<h3>Курс валюты</h3>
	<p>Валюта: <?= htmlspecialchars($arResult["CURRENCY"]) ?></p>
	<p>Курс: <?= htmlspecialchars($arResult["AMOUNT"]) ?></p>
</div>
