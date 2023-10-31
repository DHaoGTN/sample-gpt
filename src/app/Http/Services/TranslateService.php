<?php

namespace App\Http\Services;

use App\Exceptions\ErrorCallAPIException;
use App\Exceptions\ParsingAPIResponseException;
use App\Exceptions\GetTranslatedTextException;
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
    public function translate($original_text, $arrLang)
    {
        $prompt = 'Translate the following text into '.implode(', ', $arrLang).' 
        and return the result in only JSON format like {"country_code":"translated_text",...} 
        with country_code is '.implode(', ', array_keys($arrLang)).'.
        Do not return "ja" and preachy. The text is: \n """'.$original_text.'""" ';
        // $prompt = 'Translate the following text into '.implode(', ', $this->arrLang).' 
        // and no return JSON format. The text is: \n """'.$original_text.'""" ';
        Log::info('prompt: '. $prompt);
        $finalContent = $this->callAPITranslate($prompt, $original_text, $arrLang);
        // $finalContent = 
        // if call api 1 time: '{"en": "Hello. How are you", "vi": "Xin chào. Bạn khoẻ không", "zh": "你好hbjj", "ko": "dsds", "tw": "fvdvd", "pt": "r4rfd" }'; 
        // if split prompt and call api multiple times: '{"en": "Hello."} {"vi": "Xin chào."} {"zh": "你好"}...{"en": "How are you"} {"vi": "Bạn khoẻ không"} {"zh": "adcc"}...

        if (!$finalContent) throw new ErrorCallAPIException();
        // $this->parsingGPTResponseToArrayJson($finalContent); // Old method
        Log::info('finalContent: '. $finalContent);
        
        try {
            return $this->getTranslatedForEachLanguage($finalContent);
        } catch (GetTranslatedTextException $e) {
            throw new GetTranslatedTextException();
        } catch (\Exception $e) {
            throw new ParsingAPIResponseException();
        }
        
    }

    public function callAPITranslate($prompt, $original_text, $arrLang)
    {
        $numNewTokenPrompt = token_len_new($prompt);
        $numTokenPromptAllowed = (OpenAIService::MAX_TOKEN_4K / (count($arrLang)+1))-OpenAIService::BUFFER_TOKEN_FOR_SAFETY;
        Log::info('numNewTokenPrompt: '.$numNewTokenPrompt);
        if ($numNewTokenPrompt <= $numTokenPromptAllowed) {
            Log::info('4K');
            $startTime = microtime(true);
            $response = $this->openAIService->doCallAPIInstruct($prompt, OpenAIService::MAX_TOKEN_4K-$numNewTokenPrompt);
            $endTime = microtime(true);
            Log::info('time: '. $endTime-$startTime);
            Log::info('response: '. json_encode($response).'\n');
            if ($this->openAIService->getStatusAPIInstruct($response) == OpenAIService::RESPONSE_ERR_MAX_TOKEN || $this->openAIService->getStatusAPIInstruct($response) == OpenAIService::RESPONSE_ERR_STOP_BY_LENGTH) {
                Log::info('over4K');
                if (count($arrLang) > 1) {
                    return $this->splitPromptForCallAPI($original_text, $arrLang);
                } else {
                    return $this->splitPromptSentencesForCallAPI($original_text, $arrLang);
                }
                // $modelType = 'chat';
                // $response = $this->openAIService->doCallAPIChat($prompt, OpenAIService::MODEL_16K);
                // if ($this->openAIService->getStatusAPISuccess($response) == OpenAIService::RESPONSE_ERR_MAX_TOKEN) {
                //     Log::info('over16K 1');
                //     // Over 16K token, let's use splited string to divide call API into many times.
                //     return $this->splitPromptForCallAPI($original_text);
                // }
            }
        } else {
            Log::info('Split');
            if (count($arrLang) > 1 && $numNewTokenPrompt <= OpenAIService::MAX_TOKEN_PROMPT_FOR_1_LANGUAGE_4K) {
                return $this->splitPromptForCallAPI($original_text, $arrLang);
            } else {
                return $this->splitPromptSentencesForCallAPI($original_text, $arrLang);
            }
            // Log::info('16K');
            // $modelType = 'chat';
            // $response = $this->openAIService->doCallAPIChat($prompt, OpenAIService::MODEL_16K);
            // if ($this->openAIService->getStatusAPISuccess($response) == OpenAIService::RESPONSE_ERR_MAX_TOKEN) {
            //     Log::info('over16K 2');
            //     // Over 16K token, let's use splited string to divide call API into many times.
            //     return $this->splitPromptForCallAPI($original_text);
            // }
        }
        return $this->getJsonFromResponse('', $response);
    }

    public function splitPromptForCallAPI($original_text, $arrLang)
    {
        Log::info('First, split by 1 language / prompt');
        $arrPrompt = $this->createLanguagePrompts($original_text, $arrLang);
        $multipleJson = $this->callAPITranslateMultiple($arrPrompt);

        if (!$multipleJson) {
            Log::info('over max token even if 1 language, or other error -> split sentences');
            // Let's split by sentences and language
            return $this->splitPromptSentencesForCallAPI($original_text, $arrLang);
        }
        return $multipleJson;
    }
    public function splitPromptSentencesForCallAPI($original_text, $arrLang)
    {
        $arrSentencePrompts = $this->createSentencePrompts($original_text, $arrLang);
        return $this->callAPITranslateMultiple($arrSentencePrompts);
    }

    public function callAPITranslateMultiple($arrPrompt)
    {
        $arrJson = [];
        foreach ($arrPrompt as $key=>$arrLangPrompt) {
            foreach ($arrLangPrompt as $prompt) {
                sleep(2);
                Log::info('prompt-'.$key.': '.$prompt.'\n');
                $numNewTokenPrompt = token_len_new($prompt);
                $response = $this->openAIService->doCallAPIInstruct($prompt, OpenAIService::MAX_TOKEN_4K-$numNewTokenPrompt);
                $jsonContent = $this->getJsonFromResponse($key, $response);
                Log::info('response-'.$key.': '.$response);
                if (!$jsonContent) {
                    return false;
                }
                array_push($arrJson, $jsonContent);
            }
        }
        return implode(" ", $arrJson);
    }

    public function getJsonFromResponse($lang, $response, $modelType='instruct')
    {
        if ($modelType == 'chat')
            $content = $this->openAIService->getContentResponseAPIChat($response);
        else
            $content = $this->openAIService->getContentResponseAPIInstruct($response);
        
        if (!$content) return false;
        
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
            preg_match_all($patternJson, $content, $arrJson);
            
            if (sizeof($arrJson[0]) < 1) {
                if ($lang == '')
                    return json_encode($content);
                return json_encode(array($lang => $content)); // Because sometime response is no json formatting
            } else {
                return $arrJson[0][0]; // {"en": "Hello", "vi": "Xin chào", "zh": "你好" ...} or each lang: {"en": "Hello"}, ...    
            }
        } catch (\Exception $e) {
            throw new ParsingAPIResponseException();
        }
    }
    /**
     * Create array prompts for chat API by split to each language.
     * @output: array: [
     *     'en' => [
     *         'Translate the following text... whole paragraph'
     *     ],
     *     'vi' => [
     *         'Translate the following text... whole paragraph'
     *     ],...
     * ]
     */
    public function createLanguagePrompts($original_text, $arrLang)
    {
        $promptSplit = [];
        foreach ($arrLang as $key=>$value) {
            $promptSplit[$key] = array('Translate the following text into '.$value.' and return the result in only JSON format with the "'.$key.'" key. Do not return "ja" and preachy. The text is: \n """'.$original_text.'""" ');
        }
        return $promptSplit;
    }
    /**
     * Create array prompts for chat API by split original_text into sentences.
     * @output: array: [
     *     'en' => [
     *         'Translate the following text... sentence 1',
     *         'Translate the following text... sentence 2'
     *     ],
     *     'vi' => [
     *         'Translate the following text... sentence 1',
     *         'Translate the following text... sentence 2'
     *     ],...
     * ]
     */
    public function createSentencePrompts($original_text, $arrLang)
    {
        $pattern = '/(?<!Mr\.|Mrs\.|Dr\.)(?<=[.])\s+|(?<=[!?;:。！？])\s*/u';
        $arrSentences = preg_split($pattern, $original_text, -1, PREG_SPLIT_NO_EMPTY);
        $arrSentencePrompts = [];
        $arrLengthSentences = $this->makeStandardlizeLengthArray(
            $arrSentences,
            OpenAIService::MAX_TOKEN_PROMPT_FOR_1_LANGUAGE_4K-OpenAIService::BUFFER_TOKEN_FOR_SAFETY,
            OpenAIService::MAX_TOKEN_PROMPT_FOR_1_LANGUAGE_4K);
        // $samplePrompt = 'Translate this text to {{LANGUAGE}}. Must return JSON only with format {"{{COUNTRY_CODE}}":"<translated_text>"}. Do not return "ja". Do not preachy.\n {{ORIGINAL_TEXT}}';
        $samplePrompt = 'Translate the following text into {{LANGUAGE}} and return the result in only JSON format with the "{{COUNTRY_CODE}}" key. Do not return "ja" and preachy. The text is: \n {{ORIGINAL_TEXT}}';
        Log::info('*** Splitted sentences ***\n\n');
        Log::info($arrSentences);
        Log::info($arrLengthSentences);
        
        foreach ($arrLang as $key=>$value) {
            $arrSentenceLangPrompts = [];
            foreach ($arrLengthSentences as $sentence) {
                array_push($arrSentenceLangPrompts, str_replace(
                    array("{{LANGUAGE}}", "{{ORIGINAL_TEXT}}", "{{COUNTRY_CODE}}"),
                    array($value, $sentence, $key),
                    $samplePrompt));
            }
            $arrSentencePrompts[$key] = $arrSentenceLangPrompts;
        }
        return $arrSentencePrompts;
    }

    /**
     * Ensure each array element string length between min, max
     */
    public function makeStandardlizeLengthArray($arrayInput, $min, $max)
    {
        $arrayOutput = [];
        $tempString = '';
        $size = count($arrayInput);
        foreach ($arrayInput as $key=>$element) {
            $num_token_tmp_elm = token_len_new($tempString . ' ' . $element);
            $num_token_tmp = token_len_new($tempString);
            if ($num_token_tmp_elm >= $min && $num_token_tmp_elm <= $max) {
                // Add the element to tempString if concatenating results in a string length between min and max
                $tempString .= ' ' . $element;
                $arrayOutput[] = trim($tempString);
                $tempString = ''; // Reset tempString
            } elseif ($num_token_tmp_elm < $min) {
                // If concatenating results in a string length less than 20, add element to tempString
                $tempString .= ' ' . $element;
                if ($key == $size-1) {
                    // If last element, add current tempString to array output
                    $arrayOutput[] = trim($tempString);
                }
            } else {
                // If string length will exceed max, add current tempString to array output and start a new tempString
                if ($num_token_tmp >= $min && $num_token_tmp <= $max) {
                    $arrayOutput[] = trim($tempString);
                }
                $tempString = $element; // Start new tempString with current element
            }
        }

        // Handle last tempString if not empty
        if (token_len_new($tempString) >= $min && token_len_new($tempString) <= $max) {
            $arrayOutput[] = trim($tempString);
        }

        return $arrayOutput;
    }

    /**
     * Parsing json response from ChatGPT to translated string
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
        // Extract 'en', 'vn',... content using regex
        preg_match_all('/"en"\s*:\s*"(.*?)"/s', $jsonString, $enMatches);
        preg_match_all('/"vi"\s*:\s*"(.*?)"/s', $jsonString, $viMatches);
        preg_match_all('/"zh"\s*:\s*"(.*?)"/s', $jsonString, $zhMatches);
        preg_match_all('/"ko"\s*:\s*"(.*?)"/s', $jsonString, $koMatches);
        preg_match_all('/"tw"\s*:\s*"(.*?)"/s', $jsonString, $twMatches);
        preg_match_all('/"pt"\s*:\s*"(.*?)"/s', $jsonString, $ptMatches);

        // Concatenate the extracted strings for each language
        $enString = implode(' ', $enMatches[1]);
        $viString = implode(' ', $viMatches[1]);
        $zhString = implode(' ', $zhMatches[1]);
        $koString = implode(' ', $koMatches[1]);
        $twString = implode(' ', $twMatches[1]);
        $ptString = implode(' ', $ptMatches[1]);

        if (!$enString && !$viString && !$zhString && !$koString && !$twString && !$ptString) {
            throw new GetTranslatedTextException();
        }

        $translated = array();
        if ($enString) $translated['en'] = $enString;
        if ($viString) $translated['vi'] = $viString;
        if ($zhString) $translated['zh'] = $zhString;
        if ($koString) $translated['ko'] = $koString;
        if ($twString) $translated['tw'] = $twString;
        if ($ptString) $translated['pt'] = $ptString;

        return $translated;
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
