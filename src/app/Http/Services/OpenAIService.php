<?php

namespace App\Http\Services;

// use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Arr;
use Orhanerday\OpenAi\OpenAi;

class OpenAIService
{

    /**
     * Use openai-php/laravel, require php8.1
     */
    // public function callAPI($prompt)
    // {
    //     $messages = [
    //         ['role' => 'user', 'content' => $prompt],
    //     ];
    //     $response = OpenAI::chat()->create([
    //         'model' => 'gpt-3.5-turbo',
    //         'messages' => $messages,
    //     ]);
    //     return Arr::get($response, 'choices.0.message')['content'];
    // }

    /**
     * Use orhanerday/open-ai, require php7.4
     */
    public function callAPI($prompt)
    {
        $open_ai = new OpenAi(env('OPENAI_API_KEY'));

        $chat = $open_ai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    "role" => "assistant",
                    "content" => $prompt
                ]
            ],
            'temperature' => 1.0,
            'max_tokens' => 4000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);

        // dd($chat);
        // decode response
        $d = json_decode($chat);
        // Get Content
        $res = $d->choices[0]->message->content;
        return $res;
    }

}
