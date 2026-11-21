<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RemoteDeposit;
use App\Models\Transaction;
use App\Models\LevelReferral;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Traits\NotifyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemoteDepositController extends Controller
{
    use NotifyTrait;

    public function index()
    {
        $depositsQuery = RemoteDeposit::with('user')
            ->when(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth('admin')->id());
                });
            });
        admin_institution_scope($depositsQuery, 'user');
        $deposits = $depositsQuery->latest()->paginate(15);
        return view('backend.remote_deposit.index', compact('deposits'));
    }

    public function approve($id)
    {
        $deposit = RemoteDeposit::with('user')->findOrFail($id);
        
        // Security Check
        if (auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($deposit->user?->staff_id != auth('admin')->id() || !auth('admin')->user()->can('officer-deposit-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $adminName = auth('admin')->user()->name ?? 'Operations Officer';
        $res = app(\App\Services\RemoteDepositReviewService::class)->clearFullCheck((int)$id, "Admin Portal ({$adminName})");
        if (!$res['success']) {
            return redirect()->back()->with('error', $res['message']);
        }

        return redirect()->back()->with('success', 'Deposit cleared in full successfully.');
    }

    public function approveRegCc($id)
    {
        $deposit = RemoteDeposit::with('user')->findOrFail($id);
        
        // Security Check
        if (auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($deposit->user?->staff_id != auth('admin')->id() || !auth('admin')->user()->can('officer-deposit-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $res = app(\App\Services\RemoteDepositReviewService::class)->clearWithRegCcHold((int)$id, 225.00, 5);
        if (!$res['success']) {
            return redirect()->back()->with('error', $res['message']);
        }

        return redirect()->back()->with('success', 'Deposit approved with Regulation CC $225 immediate release; remainder placed on 5-business-day hold.');
    }

    public function fullHold($id)
    {
        $deposit = RemoteDeposit::with('user')->findOrFail($id);
        
        // Security Check
        if (auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($deposit->user?->staff_id != auth('admin')->id() || !auth('admin')->user()->can('officer-deposit-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $res = app(\App\Services\RemoteDepositReviewService::class)->placeOnFullHold((int)$id, 5);
        if (!$res['success']) {
            return redirect()->back()->with('error', $res['message']);
        }

        return redirect()->back()->with('success', 'Check deposit placed on standard 5-business-day hold.');
    }

    public function reject(Request $request, $id)
    {
        $deposit = RemoteDeposit::with('user')->findOrFail($id);

        // Security Check
        if (auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($deposit->user?->staff_id != auth('admin')->id() || !auth('admin')->user()->can('officer-deposit-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $reasonType = $request->input('reason_type', 'blurry');
        $res = app(\App\Services\RemoteDepositReviewService::class)->rejectForRetake((int)$id, $reasonType);
        if (!$res['success']) {
            return redirect()->back()->with('error', $res['message']);
        }

        return redirect()->back()->with('success', 'Deposit rejected / voided. Member received retake guidance email.');
    }
}
