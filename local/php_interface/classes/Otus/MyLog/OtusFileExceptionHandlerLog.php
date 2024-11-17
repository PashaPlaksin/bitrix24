<?php

namespace Otus\MyLog;

use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;

class OtusFileExceptionHandlerLog extends FileExceptionHandlerLog
{
    public function write($exception, $logType)
    {
        $result = ExceptionHandlerFormatter::format($exception);
        return $result;
    }
}

/*
 * из модуля
 * public static function format($exception, $htmlMode = false, $level = 0)
	{
		$formatter = new LogFormatter((bool)($level & static::SHOW_PARAMETERS), static::MAX_CHARS);

		$result = '';
		do
		{
			if ($result != '')
			{
				$result .= "Previous exception: ";
			}
			$result .= $formatter->format("{exception}{trace}{delimiter}\n", [
				'exception' => $exception,
				'trace' => static::getTrace($exception),
			]);
		}
		while (($exception = $exception->getPrevious()) !== null);

		if ($htmlMode)
		{
			$result = '<pre>'.Main\Text\HtmlFilter::encode($result).'</pre>';
		}

		return $result;
	}
 *
 * */