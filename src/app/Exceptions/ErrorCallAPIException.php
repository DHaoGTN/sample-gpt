<?php

namespace App\Exceptions;

class ErrorCallAPIException extends \Exception
{
    protected $message = 'Error when process or call API.';
    protected $code = 500;
}

