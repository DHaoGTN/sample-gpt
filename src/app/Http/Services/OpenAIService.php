<?php

namespace App\Http\Services;

// use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Arr;
use Orhanerday\OpenAi\OpenAi;

class OpenAIService
{
    const MODEL_4K = 'gpt-3.5-turbo';
    const MODEL_16K = 'gpt-3.5-turbo-16k';
    const MAX_TOKEN_4K = 4000;
    const MAX_TOKEN_16K = 16000;
    const NUM_LANGUAGE = 6;
    const MAX_TOKEN_PROMPT_FOR_4K = 100;
    const RESPONSE_SUCCESS = 1;
    const RESPONSE_ERR_NETWORK = 10;
    const RESPONSE_ERR_MAX_TOKEN = 20;
    const RESPONSE_ERR_OTHER = 30;

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
    public function callAPIChat($prompt, $promptSplit)
    {
        $openAi = new OpenAi(env('OPENAI_API_KEY'));
        $numTokenPrompt = sizeof(gpt_encode($prompt));
        // var_dump($numTokenPrompt);
        if ($numTokenPrompt <= self::MAX_TOKEN_PROMPT_FOR_4K) {
            // var_dump('4K');
            $response = $this->doCallAPIChat($openAi, $prompt, self::MODEL_4K, self::MAX_TOKEN_4K);
            if ($this->getStatusAPIChatSuccess($response) == self::RESPONSE_ERR_MAX_TOKEN) {
                // var_dump('over4K');
                $response = $this->doCallAPIChat($openAi, $prompt, self::MODEL_16K, self::MAX_TOKEN_16K);
            }
        } else {
            // var_dump('16K');
            $response = $this->doCallAPIChat($openAi, $prompt, self::MODEL_16K, self::MAX_TOKEN_16K);
            if ($this->getStatusAPIChatSuccess($response) == self::RESPONSE_ERR_MAX_TOKEN) {
                // var_dump('over16K');
                // Over 16K token, let's use splited string to divide call API into many times.
                // FIX ME: when I run below code, my server return 504 Gateway Time-out
                
                $arrRes = [];
                foreach ($promptSplit as $subPrompt) {
                    $response = $this->doCallAPIChat($openAi, $subPrompt, self::MODEL_16K, self::MAX_TOKEN_16K);
                    $res = $this->getContentResponseAPIChat($response);
                    if (!$res) // may get error
                        return false;
                    array_push($arrRes, $res);
                    sleep(5);
                }
                return implode(" ", $arrRes);
                
            }
        }

        $res = $this->getContentResponseAPIChat($response);
        
        return $res;
    }

    /**
     * Check for over max tokens, or anything not expected.
     * (such as OpenAI side error, network error or rate limit)
     */
    public function getStatusAPIChatSuccess($response)
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
        if ($this->getStatusAPIChatSuccess($response) == self::RESPONSE_SUCCESS) {
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

    public function doCallAPIChat($openAi, $prompt, $model, $maxToken)
    {
        return $openAi->chat([
            'model' => $model,
            'messages' => [
                [
                    "role" => "assistant",
                    "content" => $prompt
                ]
            ],
            'temperature' => 1.0,
            'max_tokens' => $maxToken,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);
    }

}
