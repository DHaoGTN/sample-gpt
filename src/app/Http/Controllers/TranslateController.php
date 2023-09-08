<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\TranslateService;
use App\Http\Requests\TranslateRequest;

class TranslateController extends Controller
{
    private TranslateService $translateService;

    public function __construct(TranslateService $translateService)
    {
        $this->translateService = $translateService;
    }

    public function index(TranslateRequest $request)
    {
        // return $request->query('text');
        try {
            return $this->translateService->translate($request->query('text'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
