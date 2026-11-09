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

        // Must be an active staff member / account officer
        $isValidOfficer = $officer && (int)$officer->status === 1;

        if ($isValidOfficer) {
            try {
                $officer->increment('onboarding_views_count');
            } catch (\Throwable $e) {
                Log::warning("Could not increment onboarding view count for officer #{$officer->id}: " . $e->getMessage());
            }

            // Set 30-day cookie (43200 minutes) and active session
            Cookie::queue('officer_onboard', (string)$officer->id, 43200);
            session(['officer_onboard' => $officer->id]);
        }

        // Silent redirect to standard registration page
        return redirect()->route('register');
    }
}

