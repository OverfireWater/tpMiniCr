<?php
declare(strict_types=1);

namespace exceptions;

use app\services\system\LangCodeServices;
use RuntimeException;
use Throwable;

/**
 * API应用错误信息
 */
class ApiException extends RuntimeException
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        if (is_array($message)) {
            $errInfo = $message;
            $message = $errInfo[1] ?? '未知错误';
            if ($code === 0) {
                $code = $errInfo[0] ?? 400;
            }
        }

        if (is_numeric($message)) {
            $code = $message;
            $message = app()->make(LangCodeServices::class)->getCodeMsg()[$message];
        }

        parent::__construct($message, $code, $previous);
    }
}
