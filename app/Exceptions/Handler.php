<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
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
        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            $adminPrefix = setting('site_admin_prefix', 'global', 'admin');
            $targetRoute = ($request->is($adminPrefix) || $request->is($adminPrefix . '/*')) ? route('admin.login-view') : route('login');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'session_expired' => true,
                    'message' => __('Your session has expired. Please sign in again to continue.'),
                    'redirect' => $targetRoute
                ], 419);
            }

            try {
                notify()->error(__('Your session has expired. Please sign in again.'), __('Session Expired'));
            } catch (\Throwable $ne) {}

            return redirect()->to($targetRoute);
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                $adminPrefix = setting('site_admin_prefix', 'global', 'admin');
                $targetRoute = ($request->is($adminPrefix) || $request->is($adminPrefix . '/*')) ? route('admin.login-view') : route('login');

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'session_expired' => true,
                        'message' => __('Your session has expired. Please sign in again to continue.'),
                        'redirect' => $targetRoute
                    ], 419);
                }

                try {
                    notify()->error(__('Your session has expired. Please sign in again.'), __('Session Expired'));
                } catch (\Throwable $ne) {}

                return redirect()->to($targetRoute);
            }
        });

        // Emergency unhandled exception capture for AJAX & API flows
        $this->renderable(function (\Throwable $e, $request) {
            // Never intercept standard framework exceptions that handle their own HTTP redirects/responses
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                if ($e->getStatusCode() < 500) {
                    return null;
                }
            }
            if (
                $e instanceof \Illuminate\Validation\ValidationException ||
                $e instanceof \Illuminate\Session\TokenMismatchException ||
                $e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Illuminate\Auth\Access\AuthorizationException
            ) {
                return null;
            }

            try {
                \Illuminate\Support\Facades\Log::error('Server Unhandled Exception: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => auth()->check() ? auth()->id() : null,
                    'trace' => substr($e->getTraceAsString(), 0, 2000),
                ]);
            } catch (\Throwable $le) {}

            $isAjax = $request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->isJson() || $request->is('api/*');
            if ($isAjax) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage() ?: 'An unexpected server error occurred. Please try again.',
                    'debug_error' => $e->getMessage(),
                    'debug_file' => basename($e->getFile()),
                    'debug_line' => $e->getLine(),
                    'debug_trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
                ], 200);
            }

            return null;
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
