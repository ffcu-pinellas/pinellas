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
        if (! $securityExpiry || ! ($securityExpiry instanceof Carbon ? $securityExpiry->isFuture() : Carbon::parse($securityExpiry)->isFuture())) {
            session()->forget('security_verified_' . $user->id);
            notify()->error(__('Security verification required to submit a transaction dispute.'));
            return redirect()->back()->withInput();
        }

        $txn = Transaction::where('user_id', $user->id)
            ->where('tnx', $tnx)
            ->firstOrFail();

        $eligibility = $this->disputeService->evaluateEligibility($txn);
        if (! $eligibility['eligible']) {
            notify()->error(__($eligibility['reason'] ?? 'Transaction is not eligible for dispute.'));
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

        $res = $this->disputeService->submitDispute($user, $txn, $validated, $evidencePath);
        if (! $res['success']) {
            notify()->error(__($res['message']));
            return redirect()->back()->withInput();
        }

        /** @var TransactionDispute $dispute */
        $dispute = $res['dispute'];
        notify()->success(__('Regulation E Dispute Claim #' . $dispute->claim_number . ' submitted successfully.'));

        return redirect()->route('user.disputes.show', $dispute->claim_number);
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
        $timeline = $dispute->timeline ?? [];
        $timeline[] = [
            'event' => 'Supporting Evidence Uploaded',
            'actor' => auth()->user()->full_name . ' (Member)',
            'at' => now('UTC')->format('M d, Y H:i \U\T\C'),
            'note' => trim((string) $request->input('evidence_note')) ?: 'Member uploaded supporting documentation (' . basename($path) . ').',
        ];

        $dispute->update([
            'evidence_path' => $path,
            'timeline' => $timeline,
        ]);

        notify()->success(__('Supporting evidence attached to Claim #' . $claimNumber . '.'));
        return redirect()->route('user.disputes.show', $claimNumber);
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

        return $pdf->download("FrontField-Dispute-Notice-{$dispute->claim_number}.pdf");
    }

    protected function storeEvidenceFile(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = 'DSP_' . Str::random(18) . '.' . $ext;
        $relativeDir = 'assets/global/images/disputes';
        $targetDir = public_path($relativeDir);

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);

        return $relativeDir . '/' . $filename;
    }
}
