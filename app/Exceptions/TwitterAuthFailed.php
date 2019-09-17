<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class TwitterAuthFailed extends Exception
{
    public function __construct($message = "Twitter Auth Failed", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
