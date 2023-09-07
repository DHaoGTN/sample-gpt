<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\EloquentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = EloquentUser::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => Hash::make('Abcd@@1234'),
            'name' => 'John Doe',
            'phone_number' => '0127656789',
        ]);;
    }
    public function testLoginWithMissingEmail()
    {
        $response = $this->postJson('/api/login', ['password' => '123123123']);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['email' => ['The email field is required.']]]);
    }

    public function testLoginWithIncorrectFormatEmail()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test',
            'password' => '123123123',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['email' => ['The email must be a valid email address.']]]);
    }

    public function testLoginWithMissingPassword()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@gmail.com',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['password' => ['The password field is required.']]]);
    }

    public function testLoginWithIncorrectCredentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@gmail.com',
            'password' => 'ga',
        ]);

        $response->assertJson(['message' => "Email or password is incorrect"])
            ->assertStatus(401);
    }


    public function testLoginWithCorrectCredentials()
    {
        $this->user;

        // Attempt login with correct credentials
        $response = $this->postJson('/api/login', [
            'email' => 'johndoe@example.com',
            'password' => 'Abcd@@1234',
        ]);
        $response->assertStatus(200);
    }


    public function testRegisterWithMissingEmail()
    {
        $response = $this->postJson('/api/register', [
            'password' => 'Abcd@@1234',
            'name' => 'John Doe',
            'phoneNumber' => '0123856789',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['email' => ['The email field is required.']]]);
    }

    public function testRegisterWithIncorrectFormatEmail()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test',
            'password' => 'Abcd@@1234',
            'name' => 'John Doe',
            'phoneNumber' => '0123856789',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['email' => ['The email must be a valid email address.']]]);
    }

    public function testRegisterWithMissingPassword()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test@gmail.com',
            'name' => 'John Doe',
            'phoneNumber' => '0123856789',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['password' => ['The password field is required.']]]);
    }

    public function testRegisterWithPasswordLower8()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test@gmail.com',
            'password' => '1234567',
            'name' => 'John Doe',
            'phoneNumber' => '0123856789',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['password' => ['The password must be at least 8 characters.']]]);
    }

    public function testRegisterWithIncorrectFormatPassword()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test@gmail.com',
            'password' => '123123123',
            'name' => 'John Doe',
            'phoneNumber' => '0123856789',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['password' => ['The password format is invalid.']]]);
    }

    public function testRegisterWithMissingName()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test@gmail.com',
            'password' => 'Abcd@@1234',
            'phoneNumber' => '0123856789',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['name' => ['The name field is required.']]]);
    }

    public function testRegisterWithMissingPhoneNumber()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test@gmail.com',
            'password' => '123123123',
            'name' => 'John Doe',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['phoneNumber' => ['The phone number field is required.']]]);
    }

    public function testRegisterWithIncorrectFormatPhoneNumber()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test@gmail.com',
            'password' => '123123123',
            'name' => 'John Doe',
            'phoneNumber' => '01238569',
        ]);

        $response->assertStatus(402)
            ->assertJson(['errors' => ['phoneNumber' => ['The phone number must be 10 characters.']]]);
    }

    public function testRegisterWithDuplicateUser()
    {
        $this->user;

        $response = $this->postJson('/api/register', [
            'email' => 'johndoe@example.com',
            'password' => 'Abcd@@1234',
            'name' => 'John Doe',
            'phoneNumber' => '0123456789',
        ]);

        $response->assertJson(["message" => "This email is already registered"])
            ->assertStatus(422);
    }

    public function testRegisterSuccessfully()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'johndoe4@example.com',
            'password' => 'Abcd@@1234',
            'name' => 'John Doe',
            'phoneNumber' => '0123856789',
        ]);

        $response->assertStatus(201)
            ->assertExactJson(['register successfully']);
    }
}
