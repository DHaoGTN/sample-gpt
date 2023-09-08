<?php

namespace App\Http\Services;

use App\Http\Services\OpenAIService;

class TranslateService
{
    private OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService) {
        $this->openAIService = $openAIService;
    }

    public function translate($original_text)
    {
        $prompt = "Can you translate this text to english, vietnamese, chinese: ".$original_text.". Please return JSON format with key is the country code, value is the text.";
        $patternJson = '
            /
            \{              # { character
                (?:         # non-capturing group
                    [^{}]   # anything that is not a { or }
                    |       # OR
                    (?R)    # recurses the entire pattern
                )*          # previous group zero or more times
            \}              # } character
            /x
            ';

        try {
            $result = $this->openAIService->callAPI($prompt);
            // $result = '{"en": "Hello", "vi": "Xin chào", "zh": "你好" }'; // or sometime GPT have say introduce at start, so need use regex to filter json
            preg_match_all($patternJson, $result, $arrTranslated);
            return $arrTranslated[0][0]; // return { "en": "Hello", "vi": "Xin chào", "zh": "你好" }

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


}
