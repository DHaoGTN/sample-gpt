<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (\Exception $e) {
            Log::error('error', ['message', $e->getMessage()]);
            Log::error('stackTrace', $e->getTrace());

            if ($e instanceof ErrorCallAPIException) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            if ($e instanceof ParsingAPIResponseException) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            if ($e instanceof GetTranslatedTextException) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
