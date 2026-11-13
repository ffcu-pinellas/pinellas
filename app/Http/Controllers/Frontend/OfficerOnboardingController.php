<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class OfficerOnboardingController extends Controller
{
    /**
     * Resolve onboarding by officer numeric ID (e.g. frontfieldcu.pro/14).
     */
    public function resolveById($officer_id): RedirectResponse
    {
        $officer = Admin::find($officer_id);

        return $this->processOfficerOnboarding($officer, (string)$officer_id);
    }

    /**
     * Resolve onboarding by vanity alias / code (e.g. frontfieldcu.pro/join/sarah).
     */
    public function resolveByCode($code): RedirectResponse
    {
        $cleanCode = trim($code);
        $officer = Admin::where('onboarding_code', $cleanCode)->first();

        if (!$officer && is_numeric($cleanCode)) {
            $officer = Admin::find($cleanCode);
        }

        return $this->processOfficerOnboarding($officer, $cleanCode);
    }

    /**
     * Process officer validation, track view count, set encrypted cookie, and redirect to registration.
     * Flow is completely silent with no visual difference from standard signup as requested.
     */
    protected function processOfficerOnboarding(?Admin $officer, string $identifier): RedirectResponse
    {
        Admin::ensureOnboardingSchema();

        $currentInstId = app()->bound('institution')
            ? (app('institution')->id ?? \App\Models\Institution::getDefault()?->id)
            : \App\Models\Institution::getDefault()?->id;

        // Must be an active staff member AND authorized for this specific institution
        $isValidOfficer = $officer && (int)$officer->status === 1 && $officer->hasInstitutionAccess($currentInstId);

        if ($isValidOfficer) {
            try {
                $officer->increment('onboarding_views_count');
            } catch (\Throwable $e) {
                Log::warning("Could not increment onboarding view count for officer #{$officer->id}: " . $e->getMessage());
            }

            // Store in session for this signup flow only (no 30-day sticky cookie that hijacks direct signups)
            session(['officer_onboard' => $officer->id]);
        }

        // Silent redirect to standard registration page
        return redirect()->route('register');
    }
}

