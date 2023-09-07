<?php

namespace App\Infrastructure\Repositories;

use App\Infrastructure\Eloquent\EloquentUser;
use App\Interfaces\UserRepositoryInterface;
use App\Packages\Domain\Common\Name;
use App\Packages\Domain\User\Email;
use App\Packages\Domain\User\PhoneNumber;
use App\Packages\Domain\User\User;
use App\Packages\Domain\User\UserId;
use Illuminate\Support\Facades\Hash;


class UserRepository implements UserRepositoryInterface
{
    public function saveNewUser(string $email, string $password, string $name, string $phoneNumber): User
    {
        return EloquentUser::create([
            'email' => $email,
            'password' => Hash::make($password),
            'name' => $name,
            'phone_number' => $phoneNumber,
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        $user = EloquentUser::searchByEmail($email)->get()->first();

        if (!$user) {
            return null;
        }

        return new User(
            new UserId($user->id),
            new Name($user->name),
            new Email($user->email),
            new PhoneNumber($user->phone_number)
        );
    }

    public function findById(int $id): ?User
    {
        $user = EloquentUser::searchById($id)->get()->first();

        if (!$user) {
            return null;
        }

        return new User(
            new UserId($user->id),
            new Name($user->name),
            new Email($user->email),
            new PhoneNumber($user->phone_number)
        );
    }

    public function findUserByEmailForLogin(string $email): EloquentUser
    {
        return EloquentUser::searchByEmail($email)->get()->first();
    }
}