<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     * @noinspection PhpFullyQualifiedNameUsageInspection
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function render($request, Throwable $exception): Response
    {
        if ($exception instanceof TokenMismatchException) {
            return redirect()->back()->
            withInput($request->except('_token'))
                ->withErrors(__('[Page expired, please try again.]'));
        }

        $response = parent::render($request, $exception);



        if ($response->status() === 419) {
            return back()->with([
                'message' => __('[Page expired, please try again.]'),
            ]);
        }

        /** @noinspection PhpFullyQualifiedNameUsageInspection */
        if($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
            $exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException ||
            $exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException){
            $error = [
                'message'=> $exception->getMessage(),
                'type'   => \get_class($exception),
                'url'    => $request->url(),
                'method' => $request->method(),
                'data'   => $request->all(),
            ];

            $message = '';
            if (method_exists($exception, 'getStatusCode')) {
                $message = $exception->getStatusCode();
            }

            $message .= ': ' . $error['url'] . "\n" . \json_encode($error, JSON_PRETTY_PRINT);

            Log::debug($message);
        }
        return parent::render($request, $exception);
    }
}
