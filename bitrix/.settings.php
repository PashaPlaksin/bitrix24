<?php


return array(
    'cache_flags' =>
        array(
            'value' =>
                array(
                    'config_options' => 3600.0,
                ),
            'readonly' => false,
        ),
    'cookies' =>
        array(
            'value' =>
                array(
                    'secure' => false,
                    'http_only' => true,
                ),
            'readonly' => false,
        ),
    'exception_handling' =>
        array(
            'value' =>
                array(
                    'debug' => true,
                    'handled_errors_types' => 4437,
                    'exception_errors_types' => 4437,
                    'ignore_silence' => false,
                    'assertion_throws_exception' => true,
                    'assertion_error_type' => 256,
                    'log' => [
                        'class_name' => 'Otus\MyLog\OtusFileExceptionHandlerLog',
                        'required_file' => 'php_interface/classes/MyLog/OtusFileExceptionHandlerLog.php',
                        'settings' =>
                            [
                                'file' => 'logs/otus_exceptions.log',
                                'log_size' => 1000000,
                            ],
                    ],
                ),
            'readonly' => false,
        ),
    'connections' =>
        array(
            'value' =>
                array(
                    'default' =>
                        array(
                            'host' => 'localhost',
                            'database' => 'ct63509_bitrix',
                            'login' => 'ct63509_bitrix',
                            'password' => '81hS7Pp3',
                            'options' => 2.0,
                            'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
                        ),
                ),
            'readonly' => true,
        ),
    'crypto' =>
        array(
            'value' =>
                array(
                    'crypto_key' => '94d26759a184188955cd493ca5ab2f6f',
                ),
            'readonly' => true,
        ),
);

