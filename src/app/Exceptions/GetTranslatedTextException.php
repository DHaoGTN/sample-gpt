<?php

namespace App\Exceptions;

class GetTranslatedTextException extends \Exception
{
    protected $message = 'Problem when get translated text (miss language).';
    protected $code = 500;
}

