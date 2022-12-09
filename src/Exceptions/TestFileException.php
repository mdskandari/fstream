<?php

namespace Mdeskandari\Fstream\Exceptions;

use Exception;

class TestFileException
{
    public int $var;

    public const  THROW_NONE = 0;
    public const THROW_CUSTOM = 1;
    public const THROW_DEFAULT = 2;

    /**
     * @throws FileException
     * @throws Exception
     */
    function __construct($value = self::THROW_NONE)
    {
        switch ($value) {
            case self::THROW_CUSTOM:
                // throw custom exception
                throw new FileException('1 is an invalid parameter', 5);
                break;

            case self::THROW_DEFAULT:
                // throw default one.
                throw new Exception('2 is not allowed as a parameter', 6);
                break;

            default:
                // No exception, object will be created.
                $this->var = $value;
                break;
        }
    }

}