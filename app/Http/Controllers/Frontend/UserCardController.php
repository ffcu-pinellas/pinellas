<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserCard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserCardController extends Controller
{
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:user_cards,id',
            'status' => 'required|boolean'
        ]);

        $card = UserCard::where('id', $request->card_id)
                        ->where('user_id', auth()->id())
                        ->firstOrFail();

        $card->status = $request->status;
        $card->save();

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

        // status 2 = Lost/Stolen (assuming 0=Inactive, 1=Active, 2=Lost)
        // Or we can add a specific 'is_lost' column, but for now let's use status 0 (Locked/Inactive) 
        // and maybe add a reason? For simplicity, we'll just lock it and notify admin.
        
        $card->status = 0; 
        $card->save();

        // Notify Admin (Implementation omitted for brevity, but would go here)

        return response()->json(['message' => 'Card reported lost and has been locked. Please contact support for a replacement.']);
    }

    public function resetPin(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:user_cards,id',
            'current_pin' => 'nullable|string', // Optional if we just verify user password instead
            'new_pin' => 'required|numeric|digits:4',
            'confirm_pin' => 'required|same:new_pin',
            'password' => 'required|string', // Verify user password for security
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['password' => 'Incorrect account password.']);
        }

        $card = UserCard::where('id', $request->card_id)
                        ->where('user_id', $user->id)
                        ->firstOrFail();
        
        // Check if current PIN matches if provided
        if ($request->filled('current_pin') && $card->pin && !Hash::check($request->current_pin, $card->pin)) {
             throw ValidationException::withMessages(['current_pin' => 'Current PIN is incorrect.']);
        }

        $card->pin = Hash::make($request->new_pin);
        $card->save();
        
        return response()->json(['message' => 'PIN updated successfully.']);
    }
}
