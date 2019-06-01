<?php

namespace Neoan3\Core;

use Exception as ExceptionAlias;
use Throwable;

class RouteException extends ExceptionAlias {
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}