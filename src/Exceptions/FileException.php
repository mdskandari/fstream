<?php

namespace Mdeskandari\Fstream\Exceptions;

use Exception;
use Throwable;

class FileException extends Exception
{

    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
        // some code
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction()
    {
        // TODO:
    }
}
