<?php

namespace App\Exceptions;

class NoUserException extends \Exception
{
    protected $message = 'User not found in the database.';
    protected $code = 404;
}
