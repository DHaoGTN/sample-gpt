<?php

namespace App\Http\Services;

// use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Orhanerday\OpenAi\OpenAi;
use App\OpenAI\MyOpenAI;

class OpenAIService
{
    const MODEL_4K = 'gpt-3.5-turbo';
    const MODEL_16K = 'gpt-3.5-turbo-16k';
    const MODEL_INSTRUCT = 'gpt-3.5-turbo-instruct';
    const MAX_TOKEN_PROMPT_FOR_4K = 900;
    const MIN_TOKEN_PROMPT_FOR_1_LANGUAGE_16K = 14000;
    const MAX_TOKEN_PROMPT_FOR_16K = 3600;
    const RESPONSE_SUCCESS = 1;
    const RESPONSE_ERR_NETWORK = 10;
    const RESPONSE_ERR_MAX_TOKEN = 20;
    const RESPONSE_ERR_OTHER = 30;

    /**
     * Use openai-php/laravel, require php8.1
     */
    // public function doCallAPIChat($prompt)
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
    public function doCallAPIChat($prompt, $model)
    {
        $openAi = new OpenAi(env('OPENAI_API_KEY'));
        return $openAi->chat([
            'model' => $model,
            'messages' => [
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            'temperature' => 0.1,
            // 'max_tokens' => 1000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);
    }
    public function doCallAPIInstruct($prompt)
    {
        // return $this->doCallAPIChat($prompt, 'gpt-3.5-turbo');
        $openAi = new MyOpenAI(env('OPENAI_API_KEY'));
        return $openAi->instruct([
            'model' => self::MODEL_INSTRUCT,
            'prompt' => $prompt,
            'max_tokens' => 3500,
            'temperature' => 0.1
        ]);
    }

    /**
     * Check for over max tokens, or anything not expected.
     * (such as OpenAI side error, network error or rate limit)
     */
    public function getStatusAPISuccess($response)
    {
        try {
            $d = json_decode($response);
            if (!isset($d)) {
                return self::RESPONSE_ERR_NETWORK;
            } else if (isset($d->error) && $d->error->code == 'context_length_exceeded') {
                return self::RESPONSE_ERR_MAX_TOKEN;
            } else if (!isset($d->choices)) {
                return self::RESPONSE_ERR_OTHER;
            }
        } catch (\Exception $e) {
            return self::RESPONSE_ERR_OTHER;
        }
        return self::RESPONSE_SUCCESS;
    }
    
    public function getContentResponseAPIChat($response)
    {
        if ($this->getStatusAPISuccess($response) == self::RESPONSE_SUCCESS) {
            try {
                $d = json_decode($response);
                $res = $d->choices[0]->message->content;
                return $res;
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }
    public function getContentResponseAPIInstruct($response)
    {
        // return $this->getContentResponseAPIChat($response);
        if ($this->getStatusAPISuccess($response) == self::RESPONSE_SUCCESS) {
            try {
                $d = json_decode($response);
                $res = $d->choices[0]->text;
                return $res;
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

}
