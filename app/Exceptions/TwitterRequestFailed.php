<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class TwitterRequestFailed extends Exception
{
    public function __construct($message = "Twitter Request Failed", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
