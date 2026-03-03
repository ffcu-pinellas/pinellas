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
            notify()->error(__('Your session has expired. Please sign in again.'), __('Page Expired'));
            
            $adminPrefix = setting('site_admin_prefix', 'global', 'admin');
            if ($request->is($adminPrefix) || $request->is($adminPrefix . '/*')) {
                return redirect()->route('admin.login-view');
            }
            
            return redirect()->route('login');
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
