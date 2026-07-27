<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;

class PasswordResetLinkController extends Controller
{
    use NotifyTrait;

    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $page = Page::where('code', 'forgetpassword')->first();
        $data = json_decode($page->data, true);

        return view('frontend::auth.forgot-password', compact('data'));
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $input = $request->input('email');

        if (empty($input)) {
            notify()->error(__('Please enter your email address or username.'), 'Error');
            return redirect()->back()->with('error', __('Please enter your email address or username.'));
        }

        $user = \App\Models\User::where('email', $input)
            ->orWhere('username', $input)
            ->first();

        if (!$user || !$user->email) {
            notify()->error(__('Email or Username not found!'), 'Error');
            return redirect()->back()->with('error', __('Email or Username not found!'));
        }

        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

        $shortcodes = [
            '[[token]]' => $url,
            '[[reset_url]]' => $url,
            '[[full_name]]' => $user->full_name ?? 'Member',
            '[[site_title]]' => setting('site_title', 'global'),
            '[[site_url]]' => route('home'),
        ];

        try {
            $this->mailNotify($user->email, 'user_password_change', $shortcodes);
        } catch (\Throwable $e) {
            \Log::error("Password reset mail sending failed for {$user->email}: " . $e->getMessage());
        }

        notify()->success(__('We have emailed your password reset link!'), 'Success');

        return redirect()->back()->with('status', __('We have emailed your password reset link!'));
    }
}
