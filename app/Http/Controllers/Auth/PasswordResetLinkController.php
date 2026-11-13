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
        $currentInstitution = app()->bound('institution') ? app('institution') : null;
        if ($currentInstitution && ! $currentInstitution->is_default) {
            $customView = 'frontend::institution.' . $currentInstitution->slug . '_forgot_password';
            if (view()->exists($customView)) {
                return view($customView, compact('currentInstitution'));
            }
        }

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
        $input = trim($request->input('email') ?? $request->input('username') ?? '');

        if (empty($input)) {
            return redirect()->back()->withErrors(['email' => __('Please enter your username or email address.')]);
        }

        // Locate member by either email or username
        $user = \App\Models\User::where('email', $input)
            ->orWhere('username', $input)
            ->first();

        if (! $user) {
            notify()->error(__('Email or Username not found!'), 'Error');

            return redirect()->back()
                ->with('error', __('The information you entered could not be properly validated.'))
                ->withErrors(['email' => __('We could not find an account matching that username or email address.')]);
        }

        $email = $user->email;
        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        $url = route('password.reset', ['token' => $token, 'email' => $email]);

        $shortcodes = [
            '[[token]]' => $url,
            '[[reset_url]]' => $url,
            '[[full_name]]' => $user->full_name ?? 'Member',
            '[[site_title]]' => setting('site_title', 'global'),
            '[[site_url]]' => route('home'),
        ];

        $this->mailNotify($email, 'user_password_change', $shortcodes);

        notify()->success(__('We have emailed your password reset link!'), __('Success'));

        return redirect()->back()->with('status', __('We have emailed your password reset link!'));
    }
}
