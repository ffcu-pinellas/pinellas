<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TransactionDispute;
use App\Services\DisputeService;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function __construct(protected DisputeService $disputeService)
    {
        $this->middleware('permission:transaction-list|officer-user-manage|officer-balance-manage');
    }

    protected function isRestrictedOfficer(): bool
    {
        $admin = auth('admin')->user();
        return $admin
            && $admin->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin')
            && ! $admin->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin');
    }

    protected function authorizeDisputeScope(TransactionDispute $dispute, bool $requireFinancialPermission = false): void
    {
        $admin = auth('admin')->user();
        if (! $admin) {
            abort(403);
        }

        if ($this->isRestrictedOfficer()) {
            if ((int) ($dispute->user?->staff_id ?? 0) !== (int) $admin->id) {
                abort(403, 'This dispute claim belongs to a member outside your assigned portfolio.');
            }
            if ($requireFinancialPermission && ! $admin->can('officer-balance-manage')) {
                abort(403, 'Only Super Admin or authorized Balance Officers can issue provisional credits or finalize disputes.');
            }
        }
    }

    public function index(Request $request)
    {
        TransactionDispute::ensureSchema();

        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));
        $adminId = auth('admin')->id();
        $isOfficer = $this->isRestrictedOfficer();

        $baseQuery = TransactionDispute::query()
            ->when($isOfficer, function ($q) use ($adminId) {
                $q->whereHas('user', fn ($u) => $u->where('staff_id', $adminId));
            });
        admin_institution_scope($baseQuery, 'user');

        $stats = [
            'all' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'provisional_credited' => (clone $baseQuery)->where('status', 'provisional_credited')->count(),
            'resolved' => (clone $baseQuery)->whereIn('status', ['resolved_fraud', 'denied'])->count(),
        ];

        $disputesQuery = TransactionDispute::with(['user', 'transaction'])
            ->when($isOfficer, function ($q) use ($adminId) {
                $q->whereHas('user', fn ($u) => $u->where('staff_id', $adminId));
            })
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('claim_number', 'LIKE', "%{$search}%")
                        ->orWhere('tnx', 'LIKE', "%{$search}%")
                        ->orWhere('merchant_name', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('first_name', 'LIKE', "%{$search}%")
                                ->orWhere('last_name', 'LIKE', "%{$search}%")
                                ->orWhere('username', 'LIKE', "%{$search}%")
                                ->orWhere('email', 'LIKE', "%{$search}%");
                        });
                });
            });
        admin_institution_scope($disputesQuery, 'user');

        $disputes = $disputesQuery
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.dispute.index', compact('disputes', 'stats', 'status', 'search'));
    }

    public function show($id)
    {
        TransactionDispute::ensureSchema();

        $dispute = TransactionDispute::with(['user', 'transaction', 'reviewer'])->findOrFail($id);
        $this->authorizeDisputeScope($dispute, false);

        $walletLabel = DisputeService::WALLET_LABELS[$dispute->wallet_type] ?? 'Checking (0010)';

        return view('backend.dispute.show', compact('dispute', 'walletLabel'));
    }

    public function viewAttachment($id)
    {
        TransactionDispute::ensureSchema();

        $dispute = TransactionDispute::with('user')->findOrFail($id);
        $this->authorizeDisputeScope($dispute, false);

        $absPath = $this->disputeService->resolveEvidenceFilePath($dispute->evidence_path);
        if (! $absPath) {
            abort(404, 'Uploaded dispute attachment could not be located on disk.');
        }

        return response()->file($absPath);
    }

    public function provisionalCredit(Request $request, $id)
    {
        $dispute = TransactionDispute::with('user')->findOrFail($id);
        $this->authorizeDisputeScope($dispute, true);

        $res = $this->disputeService->issueProvisionalCredit(
            (int) $dispute->id,
            auth('admin')->id(),
            'Web Admin',
            $request->input('resolution_notes')
        );

        notify()->{$res['success'] ? 'success' : 'error'}(__($res['message']));
        return redirect()->route('admin.disputes.show', $dispute->id);
    }

    public function resolveFraud(Request $request, $id)
    {
        $dispute = TransactionDispute::with('user')->findOrFail($id);
        $this->authorizeDisputeScope($dispute, true);

        $res = $this->disputeService->resolveAsFraud(
            (int) $dispute->id,
            auth('admin')->id(),
            'Web Admin',
            $request->input('resolution_notes')
        );

        notify()->{$res['success'] ? 'success' : 'error'}(__($res['message']));
        return redirect()->route('admin.disputes.show', $dispute->id);
    }

    public function deny(Request $request, $id)
    {
        $dispute = TransactionDispute::with('user')->findOrFail($id);
        $this->authorizeDisputeScope($dispute, true);

        $request->validate([
            'resolution_notes' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $res = $this->disputeService->denyDispute(
            (int) $dispute->id,
            auth('admin')->id(),
            'Web Admin',
            $request->input('resolution_notes')
        );

        notify()->{$res['success'] ? 'success' : 'error'}(__($res['message']));
        return redirect()->route('admin.disputes.show', $dispute->id);
    }

    public function addNote(Request $request, $id)
    {
        $dispute = TransactionDispute::with('user')->findOrFail($id);
        $this->authorizeDisputeScope($dispute, false);

        $request->validate([
            'note' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $res = $this->disputeService->addStaffNote((int) $dispute->id, auth('admin')->user(), $request->input('note'));
        notify()->{$res['success'] ? 'success' : 'error'}(__($res['message']));

        return redirect()->route('admin.disputes.show', $dispute->id);
    }
}

