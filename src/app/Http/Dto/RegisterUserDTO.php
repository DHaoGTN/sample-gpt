<?php

namespace App\Http\Dto;

class RegisterUserDTO
{
    private string $name;
    private string $email;
    private string $password;
    private string $phoneNumber;

    public function __construct(string $name, string $email, string $password, string $phoneNumber)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->phoneNumber = $phoneNumber;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}