<?php


if (file_exists(__DIR__.'/classes/autoload.php')) {
    require_once __DIR__.'/classes/autoload.php';
}

$arJsConfig = array(
    'custom_main' => array(
        'js' => '/local/js/required.js',
    )
);

foreach ($arJsConfig as $ext => $arExt) {
    \CJSCore::RegisterExt($ext, $arExt);
}


