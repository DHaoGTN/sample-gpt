<?php

namespace App\Interfaces;

use App\Infrastructure\Eloquent\EloquentUser;
use App\Packages\Domain\User\User;

interface UserRepositoryInterface
{
    public function saveNewUser(string $email, string $password, string $name, string $phoneNumber): User;

    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;

    public function findUserByEmailForLogin(string $email): EloquentUser;
}