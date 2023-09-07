<?php

namespace App\Http\Dto;

class SendMailDTO
{
    private string $mailAddress;
    private string $content;
    private string $name;
    private string $phoneNumber;

    public function __construct(string $mailAddress, string $content, string $name, string $phoneNumber)
    {
        $this->mailAddress = $mailAddress;
        $this->content = $content;
        $this->name = $name;
        $this->phoneNumber = $phoneNumber;
    }

    public function getMail(): string
    {
        return $this->mailAddress;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}
