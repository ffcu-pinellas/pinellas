<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Export\CSV\TransactionExport;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function transactions(Request $request)
    {
        $transactions = $this->getTransactionData();
        $queries = $request->query();

        return view('frontend::user.transaction.index', compact('transactions', 'queries'));
    }

    public function getTransactionData($export = false)
    {
        $from_date = trim(@explode('-', request('daterange'))[0]);
        $to_date = trim(@explode('-', request('daterange'))[1]);

        $types = match (request('type')) {
            'loan' => [TxnType::Loan->value, TxnType::LoanApply, TxnType::LoanInstallment],
            'fdr' => [TxnType::FdrIncrease, TxnType::FdrDecrease, TxnType::FdrInstallment, TxnType::FdrMaturityFee],
            'dps' => [TxnType::DpsIncrease, TxnType::DpsDecrease, TxnType::DpsInstallment, TxnType::DpsMaturity],
            default => request('type')
        };

        $transactions = Transaction::where('user_id', auth()->id())
            ->with('userWallet')
            ->search(request('trx'))
            ->when(request('daterange'), function ($query) use ($from_date, $to_date) {
                $query->whereDate('created_at', '>=', Carbon::parse($from_date)->format('Y-m-d'));
                $query->whereDate('created_at', '<=', Carbon::parse($to_date)->format('Y-m-d'));
            })
            ->when(request('type') && request('type') !== 'all', function ($query) use ($types) {
                if (is_array($types)) {
                    $query->whereIn('type', $types);
                } else {
                    $query->where('type', $types);
                }
            })
            ->latest();
        if ($export) {
            return $transactions->take(request('limit', 15))->get();
        }
        $transactions = $transactions->paginate(request('limit', 15))
            ->withQueryString();

        return $transactions;
    }

    public function transactionExportCSV()
    {
        $transactions = $this->getTransactionData(true);

        return (new TransactionExport($transactions))->download('transactions.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function transactionExportPDF(Request $request)
    {
        $period = $request->get('period', '1m');
        $selectedAccounts = $request->get('accounts', ['checking']);
        $emailStatement = $request->has('email_statement');
        $user = auth()->user();

        // Calculate date range based on period
        $to_date = now();
        $from_date = match ($period) {
            '1m' => now()->subMonth(),
            '3m' => now()->subMonths(3),
            '6m' => now()->subMonths(6),
            '1y' => now()->subYear(),
            default => now()->subMonth(),
        };

        if ($period === 'custom' && $request->has('daterange')) {
            $dates = explode(' - ', $request->get('daterange'));
            if (count($dates) == 2) {
                $from_date = Carbon::parse($dates[0]);
                $to_date = Carbon::parse($dates[1]);
            }
        }

        // Map UI account selections to database wallet_type values
        $walletTypes = [];
        if (in_array('checking', $selectedAccounts)) {
            $walletTypes[] = 'default';
            $walletTypes[] = null;
        }
        if (in_array('savings', $selectedAccounts)) $walletTypes[] = 'savings';
        if (in_array('ira', $selectedAccounts)) $walletTypes[] = 'ira';
        if (in_array('heloc', $selectedAccounts)) $walletTypes[] = 'heloc';
        if (in_array('cc', $selectedAccounts)) $walletTypes[] = 'cc';
        if (in_array('loan', $selectedAccounts)) $walletTypes[] = 'loan';

        // Fetch transactions for the selected accounts and date range
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$from_date->startOfDay(), $to_date->endOfDay()])
            ->when(!empty($walletTypes), function ($query) use ($walletTypes) {
                $query->whereIn('wallet_type', $walletTypes);
            })
            ->latest()
            ->get();

        // Masking logic for account numbers
        $mask = function($acc) {
            if (!$acc) return '';
            return '**** ' . substr($acc, -4);
        };

        $maskedAccounts = [
            'checking' => $mask($user->account_number),
            'savings' => $mask($user->savings_account_number),
            'ira' => $mask($user->ira_account_number),
            'heloc' => $mask($user->heloc_account_number),
            'cc' => $mask($user->cc_account_number),
            'loan' => $mask($user->loan_account_number),
        ];

        // Base64 Logo for PDF rendering
        $logoBase64 = null;
        try {
            $logoPath = public_path('assets/' . setting('site_logo', 'global'));
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoBase64 = 'data:image/' . $logoType . ';base64,' . base64_encode($logoData);
            }
        } catch (\Exception $e) {
            \Log::error("PDF Logo Error: " . $e->getMessage());
        }

        $pdf = Pdf::loadView('frontend::user.transaction.statement_pdf', compact('transactions', 'user', 'from_date', 'to_date', 'selectedAccounts', 'maskedAccounts', 'logoBase64'));
        
        $filename = 'eStatement_' . $user->username . '_' . now()->format('Y-m-d') . '.pdf';

        if ($emailStatement) {
            $details = [
                'subject' => 'Your Official eStatement is Ready - ' . setting('site_title', 'global'),
                'title' => 'Your Official eStatement',
                'salutation' => 'Hello ' . $user->full_name,
                'message_body' => 'Attached is your requested eStatement for the period ' . $from_date->format('M d, Y') . ' to ' . $to_date->format('M d, Y') . '. <br><br>Please download the attached PDF for your records.',
                'button_level' => 'Go to Dashboard',
                'button_link' => route('user.dashboard'),
                'footer_status' => 1,
                'bottom_status' => 0,
                'site_logo' => setting('site_logo', 'global') ? asset('assets/' . setting('site_logo', 'global')) : null,
                'site_title' => setting('site_title', 'global'),
                'site_link' => route('home'),
                'attachment' => [
                    'data' => $pdf->output(),
                    'filename' => $filename
                ]
            ];

            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\MailSend($details));
                notify()->success('eStatement has been sent to your email (' . safe($user->email) . ')', 'Success');
            } catch (\Exception $e) {
                \Log::error("eStatement email failed: " . $e->getMessage());
                notify()->error('Failed to email statement, but you can still download it.', 'Error');
            }
        }

        return $pdf->download($filename);
    }
}
