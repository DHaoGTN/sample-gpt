<?php

namespace App\Exceptions;

class DuplicateEmailException extends \Exception
{
    protected $message = 'This email is already registered';
    protected $code = 422;
}
