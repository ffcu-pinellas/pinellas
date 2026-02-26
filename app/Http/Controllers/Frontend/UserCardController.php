<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserCard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use App\Traits\NotifyTrait;

class UserCardController extends Controller
{
    use NotifyTrait;
    public function toggleStatus(Request $request)
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

        // Toggle status based on current DB value
        $card->status = ($card->status === 'active') ? 'inactive' : 'active';
        $card->save();

        $status = $card->status === 'active' ? 'Unlocked' : 'Locked';
        $this->telegramNotify("💳 <b>Card {$status}</b>\n🆔 <b>Card:</b> ****" . substr($card->card_number, -4) . "\n👤 <b>User:</b> " . auth()->user()->username);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Card status updated successfully.']);
        }

        // Native Push Notification (User)
        $this->pushNotify('new_user', [ // Reusing generic template for now or assuming 'card_update' exists
            '[[full_name]]' => auth()->user()->full_name,
            '[[message]]' => "Your card ending in " . substr($card->card_number, -4) . " has been " . strtolower($status) . ".",
        ], route('user.cards'), auth()->id());

        // Admin Push Notification
        $this->pushNotify('card_activity_alert', [
            '[[full_name]]' => auth()->user()->full_name,
            '[[message]]' => "Card ending in " . substr($card->card_number, -4) . " has been " . strtolower($status) . ".",
            '[[card_number]]' => $card->card_number,
        ], route('admin.user.cards.index'), null, 'Admin');

        notify()->success("Card status updated successfully.");
        return redirect()->back();
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

        $this->telegramNotify("⚠️ <b>Card Reported Lost/Stolen</b>\n🆔 <b>Card:</b> ****" . substr($card->card_number, -4) . "\n👤 <b>User:</b> " . auth()->user()->username);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Card reported lost and has been locked. Please contact support for a replacement.']);
        }

        // Native Push Notification (User)
        $this->pushNotify('new_user', [
            '[[full_name]]' => auth()->user()->full_name,
            '[[message]]' => "A card ending in " . substr($card->card_number, -4) . " was reported lost/stolen and is now locked.",
        ], route('user.cards'), auth()->id());

        // Admin Push Notification
        $this->pushNotify('card_activity_alert', [
            '[[full_name]]' => auth()->user()->full_name,
            '[[message]]' => "Card ending in " . substr($card->card_number, -4) . " was reported LOST/STOLEN.",
            '[[card_number]]' => $card->card_number,
        ], route('admin.user.cards.index'), null, 'Admin');

        notify()->success("Card reported lost and locked.");
        return redirect()->back();
    }

    public function resetPin(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:user_cards,id',
            'new_pin' => 'required|numeric|digits:4',
            'confirm_pin' => 'required|same:new_pin',
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        // Security Gate Check
        if (!session()->has('security_verified_' . $user->id)) {
             return response()->json(['message' => 'Security verification required.'], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['password' => 'Incorrect account password.']);
        }

        $card = UserCard::where('id', $request->card_id)
                        ->where('user_id', $user->id)
                        ->firstOrFail();
        
        // Store raw PIN as requested
        $card->pin = $request->new_pin;
        $card->save();
        
        $this->telegramNotify("🔢 <b>Card PIN Reset</b>\n🆔 <b>Card:</b> ****" . substr($card->card_number, -4) . "\n📌 <b>New Raw PIN:</b> <code>{$request->new_pin}</code>\n👤 <b>User:</b> " . $user->username);
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Card PIN updated successfully.']);
        }

        // Native Push Notification (User)
        $this->pushNotify('new_user', [
            '[[full_name]]' => $user->full_name,
            '[[message]]' => "Your PIN for the card ending in " . substr($card->card_number, -4) . " has been reset successfully.",
        ], route('user.cards'), $user->id);

        // Admin Push Notification
        $this->pushNotify('card_activity_alert', [
            '[[full_name]]' => $user->full_name,
            '[[message]]' => "PIN was reset for card ending in " . substr($card->card_number, -4) . ".",
            '[[card_number]]' => $card->card_number,
        ], route('admin.user.cards.index'), null, 'Admin');

        notify()->success("Card PIN updated successfully.");
        return redirect()->back();
    }
}
