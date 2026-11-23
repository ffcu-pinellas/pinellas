<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserCard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use App\Traits\NotifyTrait;

class UserCardController extends Controller
{
    use NotifyTrait;
    public function toggleStatus(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
            }

            // Security Gate Check - Check both session and request (fallback for mobile/AJAX flows)
            $isSecurityVerified = $request->filled('security_verified') ||
                                  $request->input('security_verified') == '1' ||
                                  session()->has('security_verified_' . $user->id);

            if (!$isSecurityVerified) {
                if ($request->wantsJson() || $request->ajax() || $request->expectsJson() || $request->is('user/cards/*')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Security verification required.'
                    ], 200);
                }
                return response()->json(['message' => 'Security verification required.'], 403);
            }

            // Resilient card resolution: Lookup by requested card_id or fallback to user's first card
            $cardId = $request->input('card_id');
            $card = null;
            if (!empty($cardId)) {
                $card = UserCard::where('id', $cardId)->where('user_id', $user->id)->first();
            }
            if (!$card) {
                $card = UserCard::where('user_id', $user->id)->first();
            }

            if (!$card) {
                if ($request->wantsJson() || $request->ajax() || $request->expectsJson() || $request->is('user/cards/*')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No active debit card record found for your account. Please contact customer support.',
                        'debug_error' => 'No card record exists in user_cards for user ' . $user->id
                    ], 200);
                }
                abort(404, 'Card not found');
            }

            // Toggle status based on current DB value
            $card->status = ($card->status === 'active') ? 'inactive' : 'active';
            $card->save();

            // Release session file lock immediately so background pollers/requests are never blocked
            try {
                if (session()->isStarted()) {
                    session()->save();
                }
            } catch (\Throwable $se) {}

            $status = $card->status === 'active' ? 'unlocked' : 'locked';
            $displayStatus = $card->status === 'active' ? 'Unlocked' : 'Locked';

            // 1. Audit via Telegram (fail-safe)
            try {
                $last4 = substr((string) ($card->card_number ?? '0000'), -4);
                $this->telegramNotify("💳 <b>Card {$displayStatus}</b>\n🆔 <b>Card:</b> ****" . $last4 . "\n👤 <b>User:</b> " . $user->username);
            } catch (\Throwable $te) {
                \Log::warning('Card toggle telegram notification warning: ' . $te->getMessage());
            }

            // 2. Dispatch Notifications (fail-safe)
            try {
                // Email Notification (Respect alert preferences)
                if (method_exists($user, 'isEmailAlertsEnabled') ? $user->isEmailAlertsEnabled() : true) {
                    $instName = $user->institution?->name ?? 'Cleveland State Bank';
                    $this->mailNotify($user->email, 'card_status_update', [
                        '[[subject]]' => "Debit Card {$displayStatus} - " . $instName,
                        '[[full_name]]' => $user->full_name,
                        '[[card_number]]' => "****" . substr((string) ($card->card_number ?? '0000'), -4),
                        '[[status]]' => $displayStatus,
                        '[[message]]' => "Your card ending in " . substr((string) ($card->card_number ?? '0000'), -4) . " has been " . $status . ". If you did not make this change, please lock your card and contact customer support immediately.",
                    ]);
                }

                // Native Push Notification (User)
                if (method_exists($user, 'isPushAlertsEnabled') ? $user->isPushAlertsEnabled() : true) {
                    $this->pushNotify('new_user', [
                        '[[full_name]]' => $user->full_name,
                        '[[message]]' => "Your card ending in " . substr((string) ($card->card_number ?? '0000'), -4) . " has been " . $status . ".",
                    ], route('user.cards'), $user->id);
                }

                // Admin Push Notification
                $this->pushNotify('card_activity_alert', [
                    '[[full_name]]' => $user->full_name,
                    '[[message]]' => "Card ending in " . substr((string) ($card->card_number ?? '0000'), -4) . " has been " . $status . ".",
                    '[[card_number]]' => $card->card_number,
                ], route('admin.cards.index'), null, 'Admin');
            } catch (\Throwable $e) {
                \Log::error("Card Status Notification Error: " . $e->getMessage());
            }

            if ($request->wantsJson() || $request->ajax() || $request->expectsJson() || $request->is('user/cards/*')) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Card has been {$status} successfully.",
                    'card_status' => $card->status
                ]);
            }

            notify()->success("Card status updated successfully.");
            return redirect()->back();
        } catch (\Throwable $e) {
            \Log::error('UserCardController::toggleStatus Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson() || $request->is('user/cards/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unable to update card status at this time: ' . $e->getMessage(),
                    'debug_error' => $e->getMessage(),
                    'debug_file' => basename($e->getFile()),
                    'debug_line' => $e->getLine()
                ], 200);
            }
            notify()->error('Unable to update card status: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function reportLost(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:user_cards,id',
        ]);

        // Security Gate Check
        if (!session()->has('security_verified_' . auth()->id())) {
             return response()->json(['message' => 'Security verification required.'], 403);
        }

        $card = UserCard::where('id', $request->card_id)
                        ->where('user_id', auth()->id())
                        ->firstOrFail();

        $card->status = 'inactive'; 
        $card->save();
        $user = auth()->user();

        // 1. Audit via Telegram
        $this->telegramNotify("⚠️ <b>Card Reported Lost/Stolen</b>\n🆔 <b>Card:</b> ****" . substr($card->card_number, -4) . "\n👤 <b>User:</b> " . $user->username);

        // 2. Dispatch Notifications BEFORE returning JSON
        try {
            // Email Notification (Respect alert preferences)
            if ($user->isEmailAlertsEnabled()) {
                $instName = $user->institution?->name ?? 'Cleveland State Bank';
                $this->mailNotify($user->email, 'card_status_update', [
                    '[[subject]]' => "Debit Card Reported Lost/Stolen - " . $instName,
                    '[[full_name]]' => $user->full_name,
                    '[[card_number]]' => "****" . substr($card->card_number, -4),
                    '[[status]]' => 'Locked (Lost/Stolen)',
                    '[[message]]' => "A card ending in " . substr($card->card_number, -4) . " was reported lost/stolen and is now locked. Please contact support immediately if you did not initiate this.",
                ]);
            }

            // Native Push Notification (User)
            if ($user->isPushAlertsEnabled()) {
                $this->pushNotify('new_user', [
                    '[[full_name]]' => $user->full_name,
                    '[[message]]' => "A card ending in " . substr($card->card_number, -4) . " was reported lost/stolen and is now locked.",
                ], route('user.cards'), $user->id);
            }

            // Admin Push Notification
            $this->pushNotify('card_activity_alert', [
                '[[full_name]]' => $user->full_name,
                '[[message]]' => "Card ending in " . substr($card->card_number, -4) . " was reported LOST/STOLEN.",
                '[[card_number]]' => $card->card_number,
            ], route('admin.cards.index'), null, 'Admin');
        } catch (\Throwable $e) {
            \Log::error("Card Report Lost Notification Error: " . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Card reported lost and has been locked. Please contact support for a replacement.']);
        }

        notify()->success("Card reported lost and locked.");
        return redirect()->back();
    }

    public function resetPin(Request $request)
    {
        $newPin = $request->new_pin ?? $request->pin;
        $confirmPin = $request->confirm_pin ?? $request->pin_confirmation;
        $request->merge([
            'new_pin' => $newPin,
            'confirm_pin' => $confirmPin,
        ]);

        $validator = Validator::make($request->all(), [
            'card_id' => 'required|exists:user_cards,id',
            'new_pin' => 'required|numeric|digits:4',
            'confirm_pin' => 'required|same:new_pin',
            'password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }
            throw new ValidationException($validator);
        }

        $user = auth()->user();

        // Security Gate Check
        $isSecurityVerified = $request->filled('security_verified') ||
                              $request->input('security_verified') == '1' ||
                              session()->has('security_verified_' . $user->id);

        if (!$isSecurityVerified) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Security verification required.'
                ], 403);
            }
            return response()->json(['message' => 'Security verification required.'], 403);
        }

        if ($request->filled('password') && !Hash::check($request->password, $user->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect account password.'
                ], 422);
            }
            throw ValidationException::withMessages(['password' => 'Incorrect account password.']);
        }

        $card = UserCard::where('id', $request->card_id)
                        ->where('user_id', $user->id)
                        ->first();

        if (!$card) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Card not found or access denied.'
                ], 422);
            }
            abort(404, 'Card not found');
        }
        
        // Store raw PIN as requested
        $card->pin = $newPin;
        $card->save();
        
        // 1. Audit via Telegram
        $this->telegramNotify("🔢 <b>Card PIN Reset</b>\n🆔 <b>Card:</b> ****" . substr($card->card_number, -4) . "\n📌 <b>New Raw PIN:</b> <code>{$newPin}</code>\n👤 <b>User:</b> " . $user->username);
        
        // 2. Dispatch Notifications BEFORE returning JSON
        try {
            // Email Notification (Respect alert preferences)
            if ($user->isEmailAlertsEnabled()) {
                $instName = $user->institution?->name ?? 'Cleveland State Bank';
                $this->mailNotify($user->email, 'card_security_update', [
                    '[[subject]]' => "Debit Card PIN Updated - " . $instName,
                    '[[full_name]]' => $user->full_name,
                    '[[card_number]]' => "****" . substr($card->card_number, -4),
                    '[[action]]' => 'PIN Reset',
                    '[[message]]' => "Your PIN for the card ending in " . substr($card->card_number, -4) . " has been reset successfully. If this wasn't you, lock your card immediately and contact support.",
                ]);
            }

            // Native Push Notification (User)
            if ($user->isPushAlertsEnabled()) {
                $this->pushNotify('new_user', [
                    '[[full_name]]' => $user->full_name,
                    '[[message]]' => "Your PIN for the card ending in " . substr($card->card_number, -4) . " has been reset successfully.",
                ], route('user.cards'), $user->id);
            }

            // Admin Push Notification
            $this->pushNotify('card_activity_alert', [
                '[[full_name]]' => $user->full_name,
                '[[message]]' => "PIN was reset for card ending in " . substr($card->card_number, -4) . ".",
                '[[card_number]]' => $card->card_number,
            ], route('admin.cards.index'), null, 'Admin');
        } catch (\Throwable $e) {
            \Log::error("Card Reset PIN Notification Error: " . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Card PIN updated successfully.']);
        }

        notify()->success("Card PIN updated successfully.");
        return redirect()->back();
    }
}
