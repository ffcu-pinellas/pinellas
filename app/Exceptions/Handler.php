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

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
