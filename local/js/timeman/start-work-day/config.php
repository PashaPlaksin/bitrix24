<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/start-work-day.bundle.css',
	'js' => 'dist/start-work-day.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];
