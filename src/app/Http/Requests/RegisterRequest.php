<?php

namespace App\Http\Requests;

use App\Http\Dto\RegisterUserDTO;

class RegisterRequest extends ApiRequest
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
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            'name' => 'required',
            'phoneNumber' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|size:10'
        ];
    }

    public function toDTO(): RegisterUserDTO
    {
        return new RegisterUserDTO(
            $this->input('name'),
            $this->input('email'),
            $this->input('password'),
            $this->input('phoneNumber')
        );
    }
}