<?php

namespace App\Http\Services;

use App\Exceptions\ErrorCallAPIException;
use App\Exceptions\ParsingAPIResponseException;
use App\Http\Services\OpenAIService;
use Illuminate\Support\Facades\Log;

class TranslateService
{
    private OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService) {
        $this->openAIService = $openAIService;
    }

    /**
     * Return array of json translated text.
     * If no need split text call API: [{"en": "Hello", "vi": "Xin chào", "zh": "你好", "ko": "Hello", "tw": "Xin chào", "pt": "你好"}]
     * If text too long, need split by language: [{"en": "Hello"}, {"vi": "Xin chào"}, ...]
     * If text too long, need split by sentence: [{"en": "Sen1"}, {"vi": "Sen1},...{"en": "Sen2"}, {"vi": "Sen2},...]
     */
    public function translate($original_text)
    {
        $prompt = 'Translate this text to english, vietnamese, chinese, korean, taiwan, portugal: '.$original_text.'. Please return JSON only with format {"en":"xxx", "vi":"xxx", "zh":"xxx", "ko":"xxx", "tw":"xxx", "pt":"xxx"}.';
        
        $finalResponse = $this->callAPITranslate($prompt, $original_text);
        // $result = 
        // if call api 1 time: '{"en": "Hello. How are you", "vi": "Xin chào. Bạn khoẻ không", "zh": "你好hbjj", "ko": "dsds", "tw": "fvdvd", "pt": "r4rfd" }'; 
        // if split prompt and call api multiple times: '{"en": "Hello."} {"vi": "Xin chào."} {"zh": "你好"}...{"en": "How are you"} {"vi": "Bạn khoẻ không"} {"zh": "adcc"}...

        if (!$finalResponse) throw new ErrorCallAPIException();
        // $this->parsingGPTResponseToArrayJson($finalResponse); // Old method
        Log::info('Final API responses: '.$finalResponse);
        
        try {
            $this->getTranslatedForEachLanguage($finalResponse);
        } catch (\Exception $e) {
            throw new ParsingAPIResponseException();
        }
        
    }

    public function callAPITranslate($prompt, $original_text)
    {
        $numTokenPrompt = sizeof(gpt_encode($prompt));
        // var_dump($numTokenPrompt);
        if ($numTokenPrompt <= OpenAIService::MAX_TOKEN_PROMPT_FOR_4K) {
            Log::info('4K');
            $response = $this->openAIService->doCallAPIChat($prompt, OpenAIService::MODEL_4K, OpenAIService::MAX_TOKEN_4K);
            if ($this->openAIService->getStatusAPIChatSuccess($response) == OpenAIService::RESPONSE_ERR_MAX_TOKEN) {
                Log::info('over4K');
                $response = $this->openAIService->doCallAPIChat($prompt, OpenAIService::MODEL_16K, OpenAIService::MAX_TOKEN_16K);
                if ($this->openAIService->getStatusAPIChatSuccess($response) == OpenAIService::RESPONSE_ERR_MAX_TOKEN) {
                    Log::info('over16K 1');
                    // Over 16K token, let's use splited string to divide call API into many times.
                    return $this->splitPromptForCallAPI($original_text);
                }
            }
        } else {
            Log::info('16K');
            $response = $this->openAIService->doCallAPIChat($prompt, OpenAIService::MODEL_16K, OpenAIService::MAX_TOKEN_16K);
            if ($this->openAIService->getStatusAPIChatSuccess($response) == OpenAIService::RESPONSE_ERR_MAX_TOKEN) {
                Log::info('over16K 2');
                // Over 16K token, let's use splited string to divide call API into many times.
                return $this->splitPromptForCallAPI($original_text);
            }
        }

        $res = $this->openAIService->getContentResponseAPIChat($response);
        
        return $res;
    }

    public function splitPromptForCallAPI($original_text)
    {
        // First, split by 1 language / prompt
        $arrPrompt = $this->createLanguagePrompts($original_text);
        $res = $this->callAPITranslateMultiple($arrPrompt);

        if (!$res) {
            Log::info('over16K even if 1 language, or other error.');
            // Let's split by sentences and language
            $arrSentencePrompts = $this->createSentencePrompts($original_text);
            $res = $this->callAPITranslateMultiple($arrSentencePrompts);
        }
        return $res;
    }

    public function callAPITranslateMultiple($arrPrompt)
    {
        $arrRes = [];
        foreach ($arrPrompt as $key=>$prompt) {
            Log::info($key); if ($key == 18) break;
            sleep(2);
            $response = $this->openAIService->doCallAPIChat($prompt, OpenAIService::MODEL_16K, OpenAIService::MAX_TOKEN_16K);
            $res = $this->openAIService->getContentResponseAPIChat($response);
            Log::info('response-'.$key.': '.$response);
            if (!$res || $this->openAIService->getStatusAPIChatSuccess($res) == OpenAIService::RESPONSE_ERR_MAX_TOKEN) {
                return false;
            }
            array_push($arrRes, $res);
        }
        return implode(" ", $arrRes);
    }

    /**
     * Create array prompts for chat API by split to each language.
     */
    public function createLanguagePrompts($original_text)
    {
        $promptSplit = [];
        array_push($promptSplit, 'Translate this text to english: '.$original_text.'. Please return JSON only with format {"en":"xxx"}.');
        array_push($promptSplit, 'Translate this text to vietnamese: '.$original_text.'. Please return JSON only with format {"vi":"xxx"}.');
        array_push($promptSplit, 'Translate this text to chinese: '.$original_text.'. Please return JSON only with format {"zh":"xxx"}.');
        array_push($promptSplit, 'Translate this text to korean: '.$original_text.'. Please return JSON only with format {"ko":"xxx"}.');
        array_push($promptSplit, 'Translate this text to taiwan: '.$original_text.'. Please return JSON only with format {"tw":"xxx"}.');
        array_push($promptSplit, 'Translate this text to portugal: '.$original_text.'. Please return JSON only with format {"pt":"xxx"}.');
        return $promptSplit;
    }
    /**
     * Create array prompts for chat API by split original_text into sentences.
     */
    public function createSentencePrompts($original_text)
    {
        $pattern = '/(?<!Mr\.|Mrs\.|Dr\.)(?<=[.])\s+|(?<=[!?;:。！？])\s*/u';
        $arrSentences = preg_split($pattern, $original_text, -1, PREG_SPLIT_NO_EMPTY);
        $arrSentencePrompts = [];
        $samplePrompt = 'Translate this text to {{LANGUAGE}}: {{ORIGINAL_TEXT}}. Please return JSON only with format {"<country_code>":"<translated_text>"}.';
        Log::info('*** Splitted sentences ***\n\n');
        foreach ($arrSentences as $sentence) {
            Log::info('- '.$sentence);
            array_push($arrSentencePrompts, str_replace(
                array("{{LANGUAGE}}", "{{ORIGINAL_TEXT}}"),
                array("english", $sentence),
                $samplePrompt));
            array_push($arrSentencePrompts, str_replace(
                array("{{LANGUAGE}}", "{{ORIGINAL_TEXT}}"),
                array("vietnamese", $sentence),
                $samplePrompt));
            array_push($arrSentencePrompts, str_replace(
                array("{{LANGUAGE}}", "{{ORIGINAL_TEXT}}"),
                array("chinese", $sentence),
                $samplePrompt));
            array_push($arrSentencePrompts, str_replace(
                array("{{LANGUAGE}}", "{{ORIGINAL_TEXT}}"),
                array("korea", $sentence),
                $samplePrompt));
            array_push($arrSentencePrompts, str_replace(
                array("{{LANGUAGE}}", "{{ORIGINAL_TEXT}}"),
                array("taiwan", $sentence),
                $samplePrompt));
            array_push($arrSentencePrompts, str_replace(
                array("{{LANGUAGE}}", "{{ORIGINAL_TEXT}}"),
                array("portugal", $sentence),
                $samplePrompt));
        }
        return $arrSentencePrompts;
    }

    /**
     * Parsing json response from ChatGPT server to translated string
     * @input: 
     * if call api 1 time: '{"en": "Hello. How are you", "vi": "Xin chào. Bạn khoẻ không", "zh": "你好hbjj", "ko": "dsds", "tw": "fvdvd", "pt": "r4rfd" }'; 
     * if split prompt and call api multiple times: '{"en": "Hello."} {"vi": "Xin chào."} {"zh": "你好"}...{"en": "How are you"} {"vi": "Bạn khoẻ không"} {"zh": "adcc"}...
     * @output:
     * English: "Hello. How are you"
     * Vietnam: "Xin chào. Bạn khoẻ không"
     * ...
     */
    public function getTranslatedForEachLanguage($jsonString)
    {
        // Extract 'en' and 'vn' content using regex
        preg_match_all('/"en": "(.*?)"/s', $jsonString, $enMatches);
        preg_match_all('/"vi": "(.*?)"/s', $jsonString, $viMatches);
        preg_match_all('/"zh": "(.*?)"/s', $jsonString, $zhMatches);
        preg_match_all('/"ko": "(.*?)"/s', $jsonString, $koMatches);
        preg_match_all('/"tw": "(.*?)"/s', $jsonString, $twMatches);
        preg_match_all('/"pt": "(.*?)"/s', $jsonString, $ptMatches);

        // Concatenate the extracted strings for each language
        $enString = implode(' ', $enMatches[1]);
        $viString = implode(' ', $viMatches[1]);
        $zhString = implode(' ', $zhMatches[1]);
        $koString = implode(' ', $koMatches[1]);
        $twString = implode(' ', $twMatches[1]);
        $ptString = implode(' ', $ptMatches[1]);

        echo "*** Translated ***\n\n";
        echo "- English: $enString\n";
        echo "- Vietnamese: $viString\n";
        echo "- Chinese: $zhString\n";
        echo "- Korean: $koString\n";
        echo "- Taiwan: $twString\n";
        echo "- Portugal: $ptString\n";
    }

    /**
     * Parsing json response from ChatGPT server to array of json
     * @input: 
     * if call api 1 time: '{"en": "Hello. How are you", "vi": "Xin chào. Bạn khoẻ không", "zh": "你好hbjj", "ko": "dsds", "tw": "fvdvd", "pt": "r4rfd" }'; 
     * if split prompt and call api multiple times: '{"en": "Hello."} {"vi": "Xin chào."} {"zh": "你好"}...{"en": "How are you"} {"vi": "Bạn khoẻ không"} {"zh": "adcc"}...
     * @output:
     * if call api 1 time: [{"en": "Hello", "vi": "Xin chào", "zh": "你好", "ko": "dsds", "tw": "fvdvd", "pt": "r4rfd" }]; 
     * if split prompt and call api multiple times: [{"en": "Hello."}, {"vi": "Xin chào."}, {"zh": "你好"},...{"en": "How are you"}, {"vi": "Bạn khoẻ không"}, {"zh": "adcc"}...
     */
    public function parsingGPTResponseToArrayJson($jsonString)
    {
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
            preg_match_all($patternJson, $jsonString, $arrTranslated);
            
            if (sizeof($arrTranslated[0]) < 1)
                return false;
            else {
                // var_dump($arrTranslated[0]);
                $arrTranslatedJsons = [];
                foreach ($arrTranslated[0] as $translated) {
                    $translatedJson = json_decode($translated); // {"en": "Hello", "vi": "Xin chào", "zh": "你好" ...} or each lang: {"en": "Hello"}, ...
                    array_push($arrTranslatedJsons, $translatedJson);
                }
                return $arrTranslatedJsons;
            }
        } catch (\Exception $e) {
            throw new ParsingAPIResponseException();
        }
    }

}
