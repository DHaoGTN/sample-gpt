<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\TranslateService;
use App\Http\Requests\TranslateRequest;
use Illuminate\Support\Facades\Log;

class TranslateController extends Controller
{
    private TranslateService $translateService;

    public function __construct(TranslateService $translateService)
    {
        $this->translateService = $translateService;
    }

    public function index(TranslateRequest $request)
    {
        return $request->query('original_text');
    }

    /**
     * @param: request:
     * translate:
     * {
     *   'original_text': 'text to translate',
     *   'langs': [
     *     {
     *       'code': 'en',
     *       'lang': 'English'
     *     },
     *     {
     *       'code': 'vi',
     *       'lang': 'Vietnam'
     *     },
     *     ...
     *   ]
     * }
     */
    public function translateMultilanguage(Request $request)
    {
        // $arrLang = array(
        //     'en'=>'English',
        //     'vi'=>'Vietnamese',
        //     'zh'=>'Chinese',
        //     'ko'=>'Korean',
        //     'tw'=>'Taiwan',
        //     'pt'=>'Portugal'
        // );
        
        try {
            $param = $request->post('translate');
            $inputData = json_decode($param, true);
            $original_text = $inputData['original_text'];
            $arrLang = [];
            // Populate the $arrLang array from the "langs" data
            if (isset($inputData['langs']) && is_array($inputData['langs'])) {
                foreach ($inputData['langs'] as $langData) {
                    if (isset($langData['code']) && isset($langData['lang'])) {
                        $arrLang[$langData['code']] = $langData['lang'];
                    }
                }
            }
            $result = $this->translateService->translate($original_text, $arrLang);
            Log::info("Translated returned...\n\n". json_encode($result));
            return json_encode($result);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
}
