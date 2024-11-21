<?php

namespace Otus\MyLog;

use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;

class OtusFileExceptionHandlerLog extends FileExceptionHandlerLog
{
    public function write($exception, $logType)
    {
        $text = ExceptionHandlerFormatter::format($exception);

        $context = [
            'type' => static::logTypeToString($logType),
        ];

        $logLevel = static::logTypeToLevel($logType);
        $message = "{date} - Host: {host} - {type} - {$text}\n";
        $lines = explode("\n", $message);

        foreach ($lines as &$line) {
            $line = 'OTUS - ' . $line;
        }

        $message = implode("\n", $lines);
        $this->logger->log($logLevel, $message, $context);
    }
}

/*
 * из модуля
 * public function write($exception, $logType)
	{
		$text = ExceptionHandlerFormatter::format($exception, false, $this->level);

		$context = [
			'type' => static::logTypeToString($logType),
		];

		$logLevel = static::logTypeToLevel($logType);

		$message = "{date} - Host: {host} - {type} - {$text}\n";

		$this->logger->log($logLevel, $message, $context);
	}


 *
 * */