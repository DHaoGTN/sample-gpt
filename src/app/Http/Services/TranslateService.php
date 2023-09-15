<?php

namespace App\Http\Services;

use App\Http\Services\OpenAIService;
use Illuminate\Support\Facades\Log;

class TranslateService
{
    private OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService) {
        $this->openAIService = $openAIService;
    }

    public function translate($original_text)
    {
        $prompt = "Can you translate this text to english, vietnamese, chinese, korean, taiwan, portugal: ".$original_text.". Please return JSON format with key is the country code, value is the text (zh for chinese, tw for taiwan).";
        $promptSplit = [];
        array_push($promptSplit, "Can you translate this text to english, vietnamese: ".$original_text.". Please return JSON format with key is the country code, value is the text.");
        array_push($promptSplit, "Can you translate this text to chinese, korean: ".$original_text.". Please return JSON format with key is the country code, value is the text (zh for chinese).");
        array_push($promptSplit, "Can you translate this text to taiwan, portugal: ".$original_text.". Please return JSON format with key is the country code, value is the text (tw for taiwan).");
        
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
            $result = $this->openAIService->callAPIChat($prompt, $promptSplit);
            // $result = 'bla bla {"en": "Hello", "vi": "Xin chào", "zh": "你好" }{"ko": "Hello", "tw": "Xin chào", "pt": "你好" }'; // or sometime GPT have say introduce at start, so need use regex to filter json
            preg_match_all($patternJson, $result, $arrTranslated);
            
            if (sizeof($arrTranslated[0]) < 1)
                return false;
            else {
                // dd($arrTranslated[0]);
                $arrTranslatedJsons = [];
                foreach ($arrTranslated[0] as $translated) {
                    $translatedJson = json_decode($translated); // return {"en": "Hello", "vi": "Xin chào", "zh": "你好" ...}
                    array_push($arrTranslatedJsons, $translatedJson);
                }
                return $arrTranslatedJsons;
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function getTranslatedLanguage($arrJson)
    // {
    //     if (sizeof($arrJson) < 1)
    //             return false;
    //     else if (sizeof($arrJson) == 1) {
    //         $d = json_decode($arrJson[0]);
    //         $en = $d->en;dd($en);
    //         $vi = $d->vi;
    //         $zh = $d->zh;
    //         $ko = $d->ko;
    //         $tw = $d->tw;
    //         $pt = $d->pt;
    //     } else if (sizeof($arrJson) == 2) {
    //         $d1 = json_decode($arrJson[0]);
    //         $en = $d1->en;
    //         $vi = $d1->vi;
    //         $zh = $d1->zh;
    //         $d2 = json_decode($arrJson[1]);
    //         $ko = $d2->ko;
    //         $tw = $d2->tw;dd($tw);
    //         $pt = $d2->pt;
    //     }
    //     // TODO: Insert to DB
    //     return true;
    // }


}
