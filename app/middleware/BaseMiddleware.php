<?php
declare (strict_types=1);

namespace app\middleware;

use app\traits\JsonReturn;

/**
 * 中间件基类
 */
class BaseMiddleware
{
    use JsonReturn;
}
