<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Subscription;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function home()
    {
        // ── Secondary Institution Dedicated Home View ───────────────────────
        $currentInstitution = app()->bound('institution') ? app('institution') : null;
        if ($currentInstitution && ! $currentInstitution->is_default) {
            // Check for dedicated institution view (e.g. clevelandstatebank.blade.php)
            if (view()->exists('frontend::institution.' . $currentInstitution->slug)) {
                return view('frontend::institution.' . $currentInstitution->slug, compact('currentInstitution'));
            }
            if (! empty($currentInstitution->aliases)) {
                foreach (explode(',', $currentInstitution->aliases) as $alias) {
                    $trimmed = trim($alias);
                    if ($trimmed && view()->exists('frontend::institution.' . $trimmed)) {
                        return view('frontend::institution.' . $trimmed, compact('currentInstitution'));
                    }
                }
            }
        }
        // ── FrontField Default Home (100% untouched) ─────────────────────────

        $customLandingTheme = Theme::where('type', 'landing')->where('status', true)->first();
        if ($customLandingTheme) {
            return view('landing_theme.'.$customLandingTheme->name);
        }

        $redirectPage = setting('home_redirect', 'global');
        if ($redirectPage == '/') {
            $homeContent = LandingPage::where('status', true)->whereNot('code', 'footer')->where('locale', app()->getLocale())->orderBy('short')->get();

            return view('frontend::home.index', compact('homeContent'));
        }

        return redirect($redirectPage);

    }

    public function subscribeNow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:subscriptions'],
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back();
        }

        Subscription::create([
            'email' => $request->email,
        ]);

        notify()->success(__('Subscribed Successfully'));

        return redirect()->back();
    }

    public function themeMode(Request $request)
    {
        $requestedMode = $request->get('mode');

        if (in_array($requestedMode, ['auto', 'dark', 'light'])) {
            session()->put('site-color-mode', $requestedMode);
            return response()->json(['status' => 'success', 'mode' => $requestedMode]);
        }

        $oldTheme = session()->get('site-color-mode', setting('default_mode'));

        if ($oldTheme == 'dark') {
            $newTheme = 'light';
        } else {
            $newTheme = 'dark';
        }

        session()->put('site-color-mode', $newTheme);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success', 'mode' => $newTheme]);
        }

        return redirect()->back();
    }

    public function languageUpdate(Request $request)
    {
        session()->put('locale', $request->name);

        return redirect()->back();
    }

    public function session(Request $request)
    {
        $key = $request->input('key');

        $value = $request->input('value');

        session([$key => $value]);

        return response()->json(['success' => true]);
    }
}
