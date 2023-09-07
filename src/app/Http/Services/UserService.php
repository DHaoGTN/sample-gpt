<?php

namespace App\Http\Services;

use App\Exceptions\DuplicateEmailException;
use App\Exceptions\NoUserException;
use App\Http\Dto\LoginUserDTO;
use App\Http\Dto\RegisterUserDTO;
use App\Infrastructure\Eloquent\EloquentUser;
use App\Interfaces\UserRepositoryInterface;
use App\Packages\Domain\User\User;

class UserService
{
    /**
     * Summary of userRepository
     * @var UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * This function checks if an email already exists in the database and throws an exception if it does.
     * 
     * @param string email The email parameter is a string that represents the email address that needs to
     * be checked for duplicates in the database.
     */
    public function checkDuplicateEmailInDatabase(string $email): void
    {
        // Check if the email is already registered
        $user = $this->userRepository->findByEmail($email);
        if ($user) {
            throw new DuplicateEmailException();
        }
    }

    /**
     * This function retrieves a user by their ID and throws an exception if the user is not found.
     * 
     * @param int id An integer representing the ID of the user to retrieve.
     * 
     * @return User This function is returning a User object.
     */
    public function getUserById(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new NoUserException();
        }

        return $user;
    }

    /**
     * The function creates a new user by passing the necessary data from a RegisterUserDTO object to the
     * userRepository.
     * 
     * @param RegisterUserDTO registerUserDTO The RegisterUserDTO is a data transfer object that contains
     * the necessary information to register a new user. It typically includes properties such as email,
     * password, name, and phone number.
     * 
     * @return User The method is returning a User object.
     */
    public function createNewUser(RegisterUserDTO $registerUserDTO): User
    {
        return $this->userRepository->saveNewUser($registerUserDTO->getEmail(), $registerUserDTO->getPassword(), $registerUserDTO->getName(), $registerUserDTO->getPhoneNumber());
    }

    /**
     * The function retrieves a user from the database based on their email for the purpose of logging in.
     * 
     * @param LoginUserDTO loginUserDTO The `LoginUserDTO` is a Data Transfer Object (DTO) that contains
     * the necessary data for user login. It typically includes properties such as email and password. In
     * this case, the `LoginUserDTO` is passed as a parameter to the `getUserForLogin` method.
     * 
     * @return EloquentUser The method is returning an instance of the EloquentUser class.
     */
    public function getUserForLogin(LoginUserDTO $loginUserDTO): EloquentUser
    {
        return $this->userRepository->findUserByEmailForLogin($loginUserDTO->getEmail());
    }
}
