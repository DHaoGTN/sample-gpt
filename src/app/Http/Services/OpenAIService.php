<?php

namespace App\Http\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Arr;

class OpenAIService
{

    public function callAPI($prompt)
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];
        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
        ]);
        return Arr::get($response, 'choices.0.message')['content'];
    }

}
