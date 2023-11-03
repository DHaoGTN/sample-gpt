<?php

namespace App\Http\Requests;
use App\Http\Dto\LoginUserDTO;

class TranslateRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'text' => 'required',
        ];
    }

}
