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
        // return $request->query('text');
        try {
            return $this->translateService->translate($request->query('text'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // $req = $request->post('text');
        // return $req;
        try {
            return $this->translateService->translate($request->post('text'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
