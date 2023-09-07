<?php

namespace App\Packages\Domain\User;

class Email
{
    private string $value;

    public function __construct(string $value)
    {
        if (!$this->isValidEmailFormat($value)) {
            throw new \Exception('Invalid email format');
        }

        $this->value = $value;
    }

    private function isValidEmailFormat(string $email): bool
    {
        $pattern = '/^\S+@\S+\.\S+$/';
        return preg_match($pattern, $email) === 1;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
