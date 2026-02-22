<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CardController extends Controller
{
    public function index()
    {
        $cards = UserCard::with('user')
            ->when(auth()->user()->hasRole('Account Officer') && !auth()->user()->hasRole('Super-Admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->latest()->paginate(10);
        return view('backend.user_card.index', compact('cards'));
    }

    public function create()
    {
        $users = User::all();
        return view('backend.user_card.create', compact('users'));
    }

    public function store(Request $request)
    {
        // Security Check
        $user = User::findOrFail($request->user_id);
        if (auth()->user()->hasRole('Account Officer') && !auth()->user()->hasRole('Super-Admin')) {
            if ($user->staff_id != auth()->id() || !auth()->user()->can('officer-card-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'status' => 'required|in:active,inactive,blocked',
        ]);

        $card = new UserCard();
        $card->user_id = $request->user_id;
        $card->card_number = $this->generateCardNumber();
        $card->card_holder_name = $user->full_name;
        $card->expiry_month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $card->expiry_year = date('Y') + rand(3, 5);
        $card->cvv = rand(100, 999);
        $card->type = $request->type;
        $card->status = $request->status;
        $card->balance = $request->balance ?? 0;
        $card->save();

        notify()->success('Card Created Successfully');
        
        if ($request->has('redirect_to')) {
            return redirect($request->redirect_to);
        }
        return redirect()->route('admin.cards.index');
    }

    public function edit($id)
    {
        $card = UserCard::find($id);
        $users = User::all();
        return view('backend.user_card.edit', compact('card', 'users'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,blocked',
            'balance' => 'numeric|min:0',
        ]);

        $card = UserCard::with('user')->findOrFail($id);

        // Security Check
        if (auth()->user()->hasRole('Account Officer') && !auth()->user()->hasRole('Super-Admin')) {
            if ($card->user?->staff_id != auth()->id() || !auth()->user()->can('officer-card-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $card->status = $request->status;
        if($request->has('balance')) {
            $card->balance = $request->balance;
        }
        $card->save();

        notify()->success('Card Updated Successfully');
        
        if ($request->has('redirect_to')) {
            return redirect($request->redirect_to);
        }
        
        // If the request came from the user edit page (via referrer check as fallback or just back)
        // But back() is safer if no redirect_to is provided and we want to return to where we came from
        return redirect()->back(); 
        // Original was redirect()->route('admin.cards.index'); but back() is better generally.
        // However, if I stick to the plan:
        // return redirect()->route('admin.cards.index');
    }

    public function destroy($id)
    {
        $card = UserCard::with('user')->findOrFail($id);

        // Security Check
        if (auth()->user()->hasRole('Account Officer') && !auth()->user()->hasRole('Super-Admin')) {
            if ($card->user?->staff_id != auth()->id() || !auth()->user()->can('officer-card-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $card->delete();
        notify()->success('Card Deleted Successfully');
        return redirect()->back();
    }

    private function generateCardNumber()
    {
        // Visa starts with 4
        return '4' . str_pad(mt_rand(1, 999999999999999), 15, '0', STR_PAD_LEFT);
    }

    public function updateCardStatus($id)
    {
        $card = UserCard::with('user')->findOrFail($id);

        // Security Check
        if (auth()->user()->hasRole('Account Officer') && !auth()->user()->hasRole('Super-Admin')) {
            if ($card->user?->staff_id != auth()->id() || !auth()->user()->can('officer-card-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($card->status == 'active') {
             $card->status = 'inactive'; 
             $msg = 'Card Frozen Successfully';
        } else {
             $card->status = 'active';
             $msg = 'Card Unfrozen Successfully';
        }
        $card->save();
        notify()->success($msg);
        return redirect()->back();
    }

    public function cardBalanceUpdate(Request $request, $id)
    {
        $card = UserCard::with('user')->findOrFail($id);

        // Security Check
        if (auth()->user()->hasRole('Account Officer') && !auth()->user()->hasRole('Super-Admin')) {
            if ($card->user?->staff_id != auth()->id() || !auth()->user()->can('officer-card-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $card->balance += $request->amount;
        $card->save();
        
        notify()->success('Card Balance Updated Successfully');
        return redirect()->back();
    }
}
