<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $loginUserDTO = $request->toDTO();

            $token = $this->authService->login($loginUserDTO);

            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(),], $e->getCode());
        }
    }

    public function register(RegisterRequest $request)
    {
        try {
            $registerUserDTO = $request->toDTO();

            $this->authService->register($registerUserDTO);

            return response()->json('register successfully', 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
