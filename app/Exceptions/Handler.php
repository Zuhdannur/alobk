<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Response;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        $rendered = parent::render($request, $exception);

        if ($this->isHttpException($exception)) {
            switch ($rendered->getStatusCode()) {
                case 403:
                    return response()->json([
                        'error' => [
                            'code' => $rendered->getStatusCode(),
                            'message' => $exception->getMessage(),
                        ]
                    ], $rendered->getStatusCode());
                    break;

                // internal error
                case 500:
                    return response()->json([
                        'error' => [
                            'code' => $rendered->getStatusCode(),
                            'message' => $exception->getMessage(),
                        ]
                    ], $rendered->getStatusCode());
                    break;
    
                // not found
                case 404:
                    return response()->json([
                        'error' => [
                            'code' => $rendered->getStatusCode(),
                            'message' => $exception->getMessage(),
                        ]
                    ], $rendered->getStatusCode());
                    break;
    
                default:
                    return response()->json([
                        'error' => [
                            'code' => $rendered->getStatusCode(),
                            'message' => $exception->getMessage(),
                        ]
                    ], $rendered->getStatusCode());
                    break;
            }
        } else {
            return parent::render($request, $exception);
        }
    }
}
