<?php

namespace App\Exceptions;

class ParsingAPIResponseException extends \Exception
{
    protected $message = 'Error when parsing API response.';
    protected $code = 500;
}

