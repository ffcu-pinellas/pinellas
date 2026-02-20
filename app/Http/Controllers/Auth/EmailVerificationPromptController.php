<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {

        if (! setting('email_verification', 'permission')) {
            return redirect()->route('user.dashboard');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            \Log::error('Mail Error: ' . $e->getMessage());
            notify()->warning(__('We were unable to send the verification email to your registered email address. Please  contact support to verify your account or check personal email settings.'), 'Mail Server Error');
        }

        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(RouteServiceProvider::HOME)
            : view('frontend::auth.verify-email');
    }
}
