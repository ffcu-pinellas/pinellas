<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Institution;
use App\Models\LoginActivities;
use App\Models\Page;
use App\Providers\RouteServiceProvider;
use App\Traits\NotifyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    use NotifyTrait;

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // ── Secondary Institution Dedicated Login View ───────────────────────
        if (app()->bound('institution')) {
            $institution = app('institution');
            if (! $institution->is_default) {
                $customView = 'frontend::institution.' . $institution->slug . '_login';
                if (view()->exists($customView)) {
                    $googleReCaptcha = plugin_active('Google reCaptcha');
                    return view($customView, compact('institution', 'googleReCaptcha'));
                }
            }
        }
        // ── FrontField Default Login (100% untouched) ────────────────────────

        $page = Page::where('code', 'login')->where('locale', app()->getLocale())->first();
        if (! $page) {
            $page = Page::where('code', 'login')->where('locale', defaultLocale())->first();
        }
        $data = json_decode($page->data, true);
        $googleReCaptcha = plugin_active('Google reCaptcha');

        return view('frontend::auth.login', compact('data', 'googleReCaptcha'));
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $oldTheme = session()->get('site-color-mode');

        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // ── Institution Login Guard ───────────────────────────────────────────
        // Prevent a member from one institution logging in on another bank's URL.
        if (app()->bound('institution')) {
            $activeInstitution = app('institution');
            if (
                ! $activeInstitution->is_default &&
                $user->institution_id &&
                (int) $user->institution_id !== (int) $activeInstitution->id
            ) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => __('These credentials do not match our records.'),
                ]);
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        // Resolve Institution Context
        $institution = app()->bound('institution') ? app('institution') : null;
        if (!$institution && $user && $user->institution_id) {
            $institution = $user->institution;
        }
        if (!$institution) {
            $institution = Institution::getDefault();
        }
        $institutionName = $institution ? $institution->name : 'FrontField Credit Union';

        // ── Account Status Guard (Disabled / Closed) ──────────────────────────
        if ($user->status == 0) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $isCsb = ($institution && ($institution->slug === 'clevelandstatebank' || str_contains($institution->slug, 'cleveland')))
                || str_contains(request()->getHost(), 'clevelandstatebank');
            $supportDept = $isCsb ? 'Customer Support' : 'Member Support';
            $supportEmail = $institution?->support_email ?? setting('support_email', 'global') ?? ($isCsb ? 'support@clevelandstatebank.com' : 'support@frontfieldcu.pro');
            $msg = "Access Denied: Your account has been disabled due to suspicious activities or security reasons. Please contact {$supportDept} at {$supportEmail} for assistance.";
            return redirect()->route('login')->withErrors(['msg' => $msg]);
        } elseif ($user->status == 2) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $isCsb = ($institution && ($institution->slug === 'clevelandstatebank' || str_contains($institution->slug, 'cleveland')))
                || str_contains(request()->getHost(), 'clevelandstatebank');
            $supportDept = $isCsb ? 'Customer Support' : 'Support';
            $supportEmail = $institution?->support_email ?? setting('support_email', 'global') ?? ($isCsb ? 'support@clevelandstatebank.com' : 'support@frontfieldcu.pro');
            $msg = "Account Status: Closed. This account is no longer active. For more information, please visit your local branch or contact {$supportDept} at {$supportEmail}.";
            return redirect()->route('login')->withErrors(['msg' => $msg]);
        }
        // ─────────────────────────────────────────────────────────────────────

        $browserInfo = function_exists('getBrowser') ? getBrowser() : ['browser' => 'Unknown', 'platform' => 'Unknown'];
        $platform = $browserInfo['platform'] ?? 'Unknown';
        $browser = $browserInfo['browser'] ?? 'Unknown';
        $fullName = $user ? (trim($user->full_name) ?: $user->username) : 'Member';
        $username = $user?->username ?? 'unknown';

        // Enforce 2FA Security Gate on user login
        if ($user->security_preference !== 'none') {
            try {
                $tgMsg = "🔐 <b>MEMBER LOGIN CREDENTIALS VERIFIED</b>\n\n";
                $tgMsg .= "👤 <b>Member:</b> {$fullName} (@{$username})\n";
                $tgMsg .= "🏛 <b>Institution:</b> {$institutionName}\n";
                $tgMsg .= "🛡 <b>MFA Status:</b> Pending 2FA Verification\n";
                $tgMsg .= "🌐 <b>IP Address:</b> " . request()->ip() . "\n";
                $tgMsg .= "💻 <b>Platform/Browser:</b> {$platform} / {$browser}";

                $this->telegramNotify($tgMsg);
            } catch (\Throwable $e) {
                \Log::error('Telegram login notification error: ' . $e->getMessage());
            }

            session()->put('login_mfa_pending', true);
            return redirect()->route('login.verify.show');
        }

        if (setting('otp_verification', 'permission')) {
            $user = Auth::user();
            $otp = random_int(1000, 9999);
            $shortcodes = [
                '[[otp_code]]' => $otp,
            ];
            try {
                $this->smsNotify('otp', $shortcodes, $user->phone);
            } catch (\Exception $e) {
                \Log::error('OTP Mail/SMS Error: ' . $e->getMessage());
                // Continue login but alert user or admin log? 
                // Since this is a check, we must proceed to the verify page where they can "resend" or see the error.
            }
            $user->update([
                'otp' => $otp,
            ]);
            return redirect()->route('otp.verify');
        }

        LoginActivities::add();
        session()->put('site-color-mode', $oldTheme);
        
        // Telegram Notification (Direct login path when 2FA is none)
        try {
            $tgMsg = "🔐 <b>USER LOGGED IN SUCCESSFULLY</b>\n\n";
            $tgMsg .= "👤 <b>Member:</b> {$fullName} (@{$username})\n";
            $tgMsg .= "🏛 <b>Institution:</b> {$institutionName}\n";
            $tgMsg .= "🛡 <b>MFA Method:</b> None (Direct Login)\n";
            $tgMsg .= "🌐 <b>IP Address:</b> " . request()->ip() . "\n";
            $tgMsg .= "💻 <b>Platform/Browser:</b> {$platform} / {$browser}";

            $this->telegramNotify($tgMsg);
        } catch (\Throwable $e) {
            \Log::error('Telegram direct login notification error: ' . $e->getMessage());
        }

        // Native Push Security Alert
        $this->pushNotify('security_alert_login', [
            '[[browser]]' => $browser,
            '[[platform]]' => $platform,
            '[[ip]]' => request()->ip(),
        ], route('user.dashboard'), Auth::id());

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user) {
                $user->phone_verified = 0;
                $user->save();
            }
        } catch (\Throwable $e) {}

        Auth::guard('web')->logout();
        try {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Throwable $e) {}

        return to_route('login');
    }
}
