<?php

namespace App\Http\Requests;
use App\Http\Dto\LoginUserDTO;

class LoginRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    public function toDTO(): LoginUserDTO
    {
        return new LoginUserDTO(
            $this->input('email'),
            $this->input('password')
        );
    }
}