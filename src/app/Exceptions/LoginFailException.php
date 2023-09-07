<?php

namespace App\Exceptions;

class LoginFailException extends \Exception
{
    protected $message = 'Email or password is incorrect';
    protected $code = 401;
}
