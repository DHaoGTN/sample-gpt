<?php

namespace App\Packages\Domain\User;

use App\Packages\Domain\Common\Name;

class User
{
    private UserId $id;
    private Name $name;
    private Email $email;
    private PhoneNumber $phoneNumber;

    /**
     * User constructor.
     * @param UserId $userId
     * @param Name $name
     * @param Email $email
     * @param PhoneNumber $phoneNumber
     */
    public function __construct(
        UserId $userId,
        Name $name,
        Email $email,
        PhoneNumber $phoneNumber
    ) {
        $this->id = $userId;
        $this->name = $name;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * The function `getName()` returns the `Name` object.
     * 
     * @return Name The method is returning an object of type Name.
     */
    public function getName(): Name
    {
        return $this->name;
    }

    /**
     * The function "getPhoneNumber" returns the phone number associated with the object.
     * 
     * @return PhoneNumber The method is returning an object of type PhoneNumber.
     */
    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id->toInt();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name->toString();
    }

    /**
     * @return string
     */
    public function email(): string
    {
        return $this->email->toString();
    }

    /**
     * @return string
     */
    public function phoneNumber(): string
    {
        return $this->phoneNumber->toString();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'userId' => $this->id(),
            'name' => $this->name(),
            'email' => $this->email(),
            'phoneNumber' => $this->phoneNumber()
        ];
    }
}