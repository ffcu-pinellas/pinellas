<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDispute;
use App\Services\DisputeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DisputeController extends Controller
{
    public function __construct(protected DisputeService $disputeService)
    {
    }

    public function index(Request $request)
    {
        TransactionDispute::ensureSchema();

        $user = auth()->user();
        $statusFilter = $request->get('status', 'all');

        $baseQuery = TransactionDispute::where('user_id', $user->id);

        $counts = [
            'all' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->whereIn('status', ['open', 'provisional_credited'])->count(),
            'provisional_credited' => (clone $baseQuery)->where('status', 'provisional_credited')->count(),
            'resolved' => (clone $baseQuery)->whereIn('status', ['resolved_fraud', 'denied'])->count(),
        ];

        $disputes = TransactionDispute::with('transaction')
            ->where('user_id', $user->id)
            ->when($statusFilter === 'active', fn ($q) => $q->whereIn('status', ['open', 'provisional_credited']))
            ->when($statusFilter === 'provisional_credited', fn ($q) => $q->where('status', 'provisional_credited'))
            ->when($statusFilter === 'resolved', fn ($q) => $q->whereIn('status', ['resolved_fraud', 'denied']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('frontend::user.disputes.index', compact('disputes', 'counts', 'statusFilter'));
    }

    public function create(string $tnx)
    {
        TransactionDispute::ensureSchema();

        $txn = Transaction::where('user_id', auth()->id())
            ->where('tnx', $tnx)
            ->firstOrFail();

        $eligibility = $this->disputeService->evaluateEligibility($txn);

        if (! $eligibility['eligible']) {
            if (! empty($eligibility['existing_dispute'])) {
                notify()->info(__('A Regulation E dispute claim already exists for this transaction.'));
                return redirect()->route('user.disputes.show', $eligibility['existing_dispute']->claim_number);
            }
            notify()->error(__($eligibility['reason'] ?? 'This transaction is not eligible to be disputed.'));
            return redirect()->route('user.transactions');
        }

        $categories = TransactionDispute::CATEGORIES;
        $maxAmount = $eligibility['max_amount'];
        $walletLabel = DisputeService::WALLET_LABELS[$eligibility['wallet_key']] ?? 'Checking (0010)';

        return view('frontend::user.disputes.create', compact('txn', 'categories', 'maxAmount', 'walletLabel'));
    }

    public function store(Request $request, string $tnx)
    {
        TransactionDispute::ensureSchema();

        $user = auth()->user();

        // Security Gate verification check (matches FundTransferController pattern)
        $securityExpiry = session('security_verified_' . $user->id);
        $hasSecurityFlag = $request->input('security_verified') == '1';
        $isSessionValid = false;
        try {
            if ($securityExpiry) {
                $isSessionValid = ($securityExpiry instanceof Carbon) ? $securityExpiry->isFuture() : Carbon::parse($securityExpiry)->isFuture();
            }
        } catch (\Throwable $t) {
            $isSessionValid = false;
        }

        if (! $isSessionValid && ! $hasSecurityFlag) {
            session()->forget('security_verified_' . $user->id);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => __('Security verification required to submit a transaction dispute.')], 403);
            }
            notify()->error(__('Security verification required to submit a transaction dispute.'));
            return redirect()->back()->withInput();
        }

        if ($hasSecurityFlag) {
            session()->put('security_verified_' . $user->id, now()->addMinutes(10));
        }

        $txn = Transaction::where('user_id', $user->id)
            ->where('tnx', $tnx)
            ->firstOrFail();

        $eligibility = $this->disputeService->evaluateEligibility($txn);
        if (! $eligibility['eligible']) {
            $reason = __($eligibility['reason'] ?? 'Transaction is not eligible for dispute.');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $reason], 422);
            }
            notify()->error($reason);
            return redirect()->route('user.transactions');
        }

        $maxAmount = $eligibility['max_amount'];

        $validated = $request->validate([
            'category' => ['required', 'in:' . implode(',', array_keys(TransactionDispute::CATEGORIES))],
            'other_reason' => ['required_if:category,other', 'nullable', 'string', 'min:3', 'max:200'],
            'disputed_amount' => ['required', 'numeric', 'min:0.01', 'max:' . $maxAmount],
            'merchant_name' => ['nullable', 'string', 'max:200'],
            'description' => ['required', 'string', 'min:15', 'max:2000'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'attestation' => ['accepted'],
        ], [
            'other_reason.required_if' => __('Please specify your dispute reason when selecting "Other".'),
            'description.min' => __('Please provide at least 15 characters describing why you are disputing this transaction.'),
            'attestation.accepted' => __('Please confirm the Regulation E Good-Faith Declaration before submitting.'),
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $this->storeEvidenceFile($request->file('evidence'));
        }

        try {
            $res = $this->disputeService->submitDispute($user, $txn, $validated, $evidencePath);
            if (! $res['success']) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => __($res['message'])], 422);
                }
                notify()->error(__($res['message']));
                return redirect()->back()->withInput();
            }

            // Release session write lock so pollers are not delayed
            try {
                if (session()->isStarted()) {
                    session()->save();
                }
            } catch (\Throwable $se) {}

            /** @var TransactionDispute $dispute */
            $dispute = $res['dispute'];
            $successMsg = __('Regulation E Dispute Claim #' . $dispute->claim_number . ' submitted successfully.');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $successMsg,
                    'claim_number' => $dispute->claim_number,
                    'redirect' => route('user.disputes.show', $dispute->claim_number),
                ]);
            }

            notify()->success($successMsg);
            return redirect()->route('user.disputes.show', $dispute->claim_number);
        } catch (\Throwable $e) {
            \Log::error('Dispute store controller error: ' . $e->getMessage());
            // Check if claim was created despite any notification error
            $existing = TransactionDispute::where('transaction_id', $txn->id)->latest('id')->first();
            if ($existing) {
                $successMsg = __('Regulation E Dispute Claim #' . $existing->claim_number . ' submitted successfully.');
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => $successMsg,
                        'claim_number' => $existing->claim_number,
                        'redirect' => route('user.disputes.show', $existing->claim_number),
                    ]);
                }
                notify()->success($successMsg);
                return redirect()->route('user.disputes.show', $existing->claim_number);
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => __('Failed to submit dispute: ') . $e->getMessage()], 422);
            }
            notify()->error(__('Failed to submit dispute: ') . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(string $claimNumber)
    {
        TransactionDispute::ensureSchema();

        $dispute = TransactionDispute::with(['transaction', 'reviewer'])
            ->where('user_id', auth()->id())
            ->where('claim_number', $claimNumber)
            ->firstOrFail();

        $walletLabel = DisputeService::WALLET_LABELS[$dispute->wallet_type] ?? 'Checking (0010)';

        return view('frontend::user.disputes.show', compact('dispute', 'walletLabel'));
    }

    public function addEvidence(Request $request, string $claimNumber)
    {
        TransactionDispute::ensureSchema();

        $dispute = TransactionDispute::where('user_id', auth()->id())
            ->where('claim_number', $claimNumber)
            ->firstOrFail();

        if (! $dispute->isActive()) {
            notify()->error(__('Evidence cannot be added once a claim has been finalized.'));
            return redirect()->route('user.disputes.show', $claimNumber);
        }

        $request->validate([
            'evidence' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'evidence_note' => ['nullable', 'string', 'max:400'],
        ]);

        $path = $this->storeEvidenceFile($request->file('evidence'));
        $note = trim((string) $request->input('evidence_note'));
        $timeline = $dispute->timeline ?? [];
        $timeline[] = [
            'event' => 'Supporting Evidence Uploaded',
            'actor' => auth()->user()->full_name . ' (Member)',
            'at' => now('UTC')->format('M d, Y H:i \U\T\C'),
            'note' => $note ?: 'Member uploaded supporting documentation (' . basename($path) . ').',
        ];

        $dispute->update([
            'evidence_path' => $path,
            'timeline' => $timeline,
        ]);

        $this->disputeService->notifyAdditionalEvidence(
            $dispute->fresh(),
            auth()->user(),
            $path,
            $note !== '' ? $note : null
        );

        notify()->success(__('Supporting evidence attached to Claim #' . $claimNumber . '.'));
        return redirect()->route('user.disputes.show', $claimNumber);
    }

    public function viewAttachment(string $claimNumber)
    {
        TransactionDispute::ensureSchema();

        $dispute = TransactionDispute::where('user_id', auth()->id())
            ->where('claim_number', $claimNumber)
            ->firstOrFail();

        $absPath = $this->disputeService->resolveEvidenceFilePath($dispute->evidence_path);
        if (! $absPath) {
            abort(404, 'Supporting documentation file could not be located.');
        }

        return response()->file($absPath);
    }

    public function downloadNotice(string $claimNumber)
    {
        TransactionDispute::ensureSchema();

        $dispute = TransactionDispute::with(['transaction', 'user'])
            ->where('user_id', auth()->id())
            ->where('claim_number', $claimNumber)
            ->firstOrFail();

        $walletLabel = DisputeService::WALLET_LABELS[$dispute->wallet_type] ?? 'Checking (0010)';
        $pdf = Pdf::loadView('frontend::user.disputes.notice_pdf', compact('dispute', 'walletLabel'));

        $user = auth()->user();
        $inst = $user ? $user->institution : null;
        $slug = ($inst && !$inst->is_default) ? Str::slug($inst->name) : 'FrontField';

        return $pdf->download("{$slug}-Dispute-Notice-{$dispute->claim_number}.pdf");
    }

    protected function storeEvidenceFile(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = 'DSP_' . Str::random(18) . '.' . $ext;
        $relativeDir = 'assets/global/images/disputes';
        // Write to root assets/ when present (Hostinger public root), else Laravel public_path
        $targetDir = File::isDirectory(base_path('assets'))
            ? base_path($relativeDir)
            : public_path($relativeDir);

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);

        return $relativeDir . '/' . $filename;
    }
}
