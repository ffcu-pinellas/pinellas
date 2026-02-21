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

        $card = UserCard::where('id', $request->card_id)
                        ->where('user_id', auth()->id())
                        ->firstOrFail();

        // Toggle status based on current DB value
        $card->status = ($card->status === 'active') ? 'inactive' : 'active';
        $card->save();

        $status = $card->status === 'active' ? 'Unlocked' : 'Locked';
        $this->telegramNotify("💳 <b>Card {$status}</b>\n🆔 <b>Card:</b> ****" . substr($card->card_number, -4));

        return response()->json(['message' => 'Card status updated successfully.']);
    }

    public function reportLost(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:user_cards,id',
        ]);

        $card = UserCard::where('id', $request->card_id)
                        ->where('user_id', auth()->id())
                        ->firstOrFail();

        $card->status = 'inactive'; 
        $card->save();

        $this->telegramNotify("⚠️ <b>Card Reported Lost/Stolen</b>\n🆔 <b>Card:</b> ****" . substr($card->card_number, -4));

        return response()->json(['message' => 'Card reported lost and has been locked. Please contact support for a replacement.']);
    }

    public function resetPin(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:user_cards,id',
            'current_pin' => 'nullable|string',
            'new_pin' => 'required|numeric|digits:4',
            'confirm_pin' => 'required|same:new_pin',
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['password' => 'Incorrect account password.']);
        }

        $card = UserCard::where('id', $request->card_id)
                        ->where('user_id', $user->id)
                        ->firstOrFail();
        
        if ($request->filled('current_pin') && $card->pin && !Hash::check($request->current_pin, $card->pin)) {
             throw ValidationException::withMessages(['current_pin' => 'Current PIN is incorrect.']);
        }

        $card->pin = Hash::make($request->new_pin);
        $card->save();
        
        $this->telegramNotify("🔢 <b>Card PIN Reset</b>\n🆔 <b>Card:</b> ****" . substr($card->card_number, -4));
        
        return response()->json(['message' => 'PIN updated successfully.']);
    }
}
