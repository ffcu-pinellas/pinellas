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
        \App\Models\TransactionDispute::ensureSchema();
        $transactions = $this->getTransactionData();
        $queries = $request->query();
        $disputeMap = \App\Models\TransactionDispute::where('user_id', auth()->id())
            ->get()
            ->keyBy('transaction_id');
        $activeDisputeCount = $disputeMap->whereIn('status', ['open', 'provisional_credited'])->count();

        return view('frontend::user.transaction.index', compact('transactions', 'queries', 'disputeMap', 'activeDisputeCount'));
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

        $wallet = request('wallet');
        $walletTypes = null;
        if ($wallet) {
            $w = strtolower(trim($wallet));
            if (in_array($w, ['checking', 'default'])) {
                $walletTypes = ['default', null, '', 'checking'];
            } elseif (in_array($w, ['savings', 'primary_savings'])) {
                $walletTypes = ['primary_savings', 'savings', 'savings_primary'];
            } elseif ($w === 'ira') {
                $walletTypes = ['ira'];
            } elseif ($w === 'heloc') {
                $walletTypes = ['heloc'];
            } elseif (in_array($w, ['cc', 'credit_card'])) {
                $walletTypes = ['cc', 'credit_card'];
            } elseif (in_array($w, ['loan', 'loan_account'])) {
                $walletTypes = ['loan', 'loan_account'];
            } else {
                $walletTypes = [$wallet];
            }
        }

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
            ->when($walletTypes, function ($query) use ($walletTypes) {
                $includesNull = in_array(null, $walletTypes, true);
                $nonNullTypes = array_filter($walletTypes, fn($t) => $t !== null);
                $query->where(function ($sub) use ($nonNullTypes, $includesNull) {
                    if (!empty($nonNullTypes)) {
                        $sub->whereIn('wallet_type', $nonNullTypes);
                    }
                    if ($includesNull) {
                        $sub->orWhereNull('wallet_type');
                    }
                });
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
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $period = $request->get('period', '1m');
        $selectedAccounts = (array) $request->get('accounts', ['checking']);
        if (in_array('all', $selectedAccounts) || empty($selectedAccounts)) {
            $selectedAccounts = ['checking', 'savings', 'ira', 'heloc', 'cc', 'loan'];
        }
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

        if ($period === 'custom') {
            if ($request->has('from_date') && $request->has('to_date') && $request->from_date != '' && $request->to_date != '') {
                $from_date = Carbon::parse($request->get('from_date'));
                $to_date = Carbon::parse($request->get('to_date'));
            } elseif ($request->has('daterange') && $request->daterange != '') {
                $dates = explode(' - ', $request->get('daterange'));
                if (count($dates) == 2) {
                    $from_date = Carbon::parse($dates[0]);
                    $to_date = Carbon::parse($dates[1]);
                }
            }
        }

        // Map UI account selections to database wallet_type values
        $isConsolidatedAll = in_array('all', (array) $request->get('accounts', [])) || empty($request->get('accounts'));
        $walletTypes = [];
        if (!$isConsolidatedAll) {
            if (in_array('checking', $selectedAccounts)) {
                $walletTypes[] = 'default';
                $walletTypes[] = 'checking';
                $walletTypes[] = null;
            }
            if (in_array('savings', $selectedAccounts)) {
                $walletTypes[] = 'savings';
                $walletTypes[] = 'primary_savings';
            }
            if (in_array('ira', $selectedAccounts)) $walletTypes[] = 'ira';
            if (in_array('heloc', $selectedAccounts)) $walletTypes[] = 'heloc';
            if (in_array('cc', $selectedAccounts)) $walletTypes[] = 'cc';
            if (in_array('loan', $selectedAccounts)) $walletTypes[] = 'loan';
        }

        // Fetch transactions for the selected accounts and date range
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$from_date->startOfDay(), $to_date->endOfDay()])
            ->when(!empty($walletTypes), function ($query) use ($walletTypes) {
                $includesNull = in_array(null, $walletTypes, true);
                $nonNullTypes = array_filter($walletTypes, fn($t) => $t !== null);
                $query->where(function ($sub) use ($nonNullTypes, $includesNull) {
                    if (!empty($nonNullTypes)) {
                        $sub->whereIn('wallet_type', $nonNullTypes);
                    }
                    if ($includesNull) {
                        $sub->orWhereNull('wallet_type');
                    }
                });
            })
            ->latest()
            ->get();
            
        \Log::info("eStatement Generation Detail: User #{$user->id}, TXN Count: " . $transactions->count() . " for period " . $from_date->toDateString() . " to " . $to_date->toDateString());

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

        // Resolve Institution Context
        $institution = $user->institution ?? \App\Models\Institution::getDefault();
        $isCustom = $institution && !$institution->is_default;

        // Base64 Logo for PDF rendering
        $logoBase64 = null;
        if ($isCustom) {
            $instLogoPath = $institution->logo_path ? public_path('assets/' . $institution->logo_path) : null;
            if (!$instLogoPath || !file_exists($instLogoPath)) {
                $instLogoPath = public_path('assets/institutions/clevelandstatebank/images/logo.png');
            }
            if (file_exists($instLogoPath)) {
                try {
                    $logoData = file_get_contents($instLogoPath);
                    if ($logoData) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                    }
                } catch (\Exception $e) {
                    \Log::error("PDF Local Logo Read Error: " . $e->getMessage());
                }
            }
        } else {
            $siteLogo = setting('site_logo', 'global');
            $logoPath = $siteLogo ? public_path('assets/' . $siteLogo) : null;
            if ($logoPath && file_exists($logoPath)) {
                try {
                    $logoData = file_get_contents($logoPath);
                    if ($logoData) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                    }
                } catch (\Exception $e) {
                    \Log::error("PDF Local Logo Read Error: " . $e->getMessage());
                }
            }
            if (!$logoBase64) {
                try {
                    $fallbackPath = public_path('assets/global/images/6RR9UFs6kLq67BrPItMv.png');
                    if (file_exists($fallbackPath)) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($fallbackPath));
                    }
                } catch (\Exception $e) {
                    \Log::error("PDF Local Fallback Logo Read Error: " . $e->getMessage());
                }
            }
        }

        $siteTitle = $institution ? $institution->name : (setting('site_title', 'global') ?? 'FrontField FCU');
        $primaryColor = $institution->primary_color ?? ($isCustom ? '#c8232c' : '#00549b');
        $regulatorBadge = $isCustom
            ? ('Member ' . ($institution->regulator_type ? strtoupper($institution->regulator_type) : 'FDIC') . ' • Equal Housing Lender')
            : 'Federally Insured by NCUA • Equal Housing Lender';
        $contactAddress = $isCustom
            ? ($institution->mailing_address ?: 'Corporate Offices • P.O. Box 278')
            : 'Corporate Offices • P.O. Box 2300';
        $contactCityStateZip = $isCustom
            ? ($institution->mailing_city_state_zip ?: 'Cleveland, MS 38732')
            : 'Cleveland, OH 44166';
        $contactPhone = $isCustom
            ? ($institution->support_phone ?: '662-843-9461')
            : (setting('site_phone', 'global') ?: '216 230 1837');
        $customDomain = $institution?->custom_domain;
        if ($customDomain && !str_ends_with(strtolower($customDomain), '.frontfieldcu.pro')) {
            $homeDomain = $customDomain;
            $siteLink = 'https://' . ltrim($customDomain, 'https://');
        } elseif ($isCustom) {
            $homeDomain = 'clevelandstatebank.com';
            $siteLink = '#';
        } else {
            $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'frontfieldcu.pro';
            $homeDomain = !str_ends_with(strtolower($appHost), '.frontfieldcu.pro') || $appHost === 'frontfieldcu.pro' ? $appHost : 'frontfieldcu.pro';
            $siteLink = route('home');
        }

        $instLogo = $institution?->logo_path;
        if ($instLogo && !\Illuminate\Support\Str::startsWith($instLogo, ['assets/', 'storage/'])) {
            $instLogo = 'assets/' . $instLogo;
        }
        $siteLogoUrl = $instLogo ? asset($instLogo) : ($isCustom ? asset('assets/institutions/clevelandstatebank/images/logo.png') : (setting('site_logo', 'global') ? asset('assets/' . setting('site_logo', 'global')) : null));

        $pdf = Pdf::loadView('frontend::user.transaction.statement_pdf', compact(
            'transactions', 'user', 'from_date', 'to_date', 'selectedAccounts', 'maskedAccounts', 
            'logoBase64', 'siteTitle', 'homeDomain', 'primaryColor', 'regulatorBadge', 
            'contactAddress', 'contactCityStateZip', 'contactPhone'
        ));
        
        $filename = 'eStatement_' . $user->username . '_' . now()->format('Y-m-d') . '.pdf';

        if ($emailStatement) {
            $details = [
                'subject' => 'Your Official eStatement is Ready - ' . $siteTitle,
                'title' => 'Your Official eStatement',
                'salutation' => 'Hello ' . $user->full_name,
                'message_body' => 'Attached is your requested eStatement for the period ' . $from_date->format('M d, Y') . ' to ' . $to_date->format('M d, Y') . '. <br><br>Please download the attached PDF for your records.',
                'button_level' => 'Go to Dashboard',
                'button_link' => route('user.dashboard'),
                'footer_status' => 1,
                'bottom_status' => 0,
                'primary_color' => $primaryColor,
                'regulator_type' => $regulatorBadge,
                'support_phone' => $contactPhone,
                'site_logo' => $siteLogoUrl,
                'site_title' => $siteTitle,
                'site_link' => $siteLink,
                'attachment' => [
                    'data' => $pdf->output(),
                    'filename' => $filename
                ]
            ];

            try {
                if (!\App\Services\BrevoMailService::sendDetails($user->email, $details)) {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\MailSend($details));
                }
                notify()->success('eStatement has been sent to your email (' . safe($user->email) . ')', 'Success');
            } catch (\Throwable $e) {
                \Log::warning("eStatement email warning: " . $e->getMessage());
                notify()->info('eStatement generated. You can download your copy below.', 'eStatement');
            }
        }

        return $pdf->download($filename);
    }

    /**
     * Download a single transaction receipt as PDF (Universal Support)
     */
    public function downloadReceipt($tnx)
    {
        $transaction = Transaction::where('user_id', auth()->id())
            ->where('tnx', $tnx)
            ->firstOrFail();

        $user = auth()->user();
        
        // Resolve Institution Context
        $institution = $user->institution ?? \App\Models\Institution::getDefault();
        $isCustom = $institution && !$institution->is_default;

        // Base64 Logo for PDF rendering
        $logoBase64 = null;
        if ($isCustom) {
            $instLogoPath = $institution->logo_path ? public_path('assets/' . $institution->logo_path) : null;
            if (!$instLogoPath || !file_exists($instLogoPath)) {
                $instLogoPath = public_path('assets/institutions/clevelandstatebank/images/logo.png');
            }
            if (file_exists($instLogoPath)) {
                try {
                    $logoData = file_get_contents($instLogoPath);
                    if ($logoData) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                    }
                } catch (\Exception $e) {
                    \Log::error("Receipt PDF Local Logo Read Error: " . $e->getMessage());
                }
            }
        } else {
            $siteLogo = setting('site_logo', 'global');
            $logoPath = $siteLogo ? public_path('assets/' . $siteLogo) : null;
            if ($logoPath && file_exists($logoPath)) {
                try {
                    $logoData = file_get_contents($logoPath);
                    if ($logoData) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                    }
                } catch (\Exception $e) {
                    \Log::error("Receipt PDF Local Logo Read Error: " . $e->getMessage());
                }
            }
            if (!$logoBase64) {
                try {
                    $fallbackPath = public_path('assets/global/images/6RR9UFs6kLq67BrPItMv.png');
                    if (file_exists($fallbackPath)) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($fallbackPath));
                    }
                } catch (\Exception $e) {
                    \Log::error("Receipt PDF Local Fallback Logo Read Error: " . $e->getMessage());
                }
            }
        }

        $siteTitle = $institution ? $institution->name : (setting('site_title', 'global') ?? 'FrontField FCU');
        $primaryColor = $institution->primary_color ?? ($isCustom ? '#c8232c' : '#00549b');
        $regulatorBadge = $isCustom
            ? ('Member ' . ($institution->regulator_type ? strtoupper($institution->regulator_type) : 'FDIC') . '. Equal Housing Lender.')
            : 'Federally insured by NCUA. Equal Housing Lender.';
        $contactAddress = $isCustom
            ? ($institution->mailing_address ?: 'Corporate Offices • P.O. Box 278')
            : 'Corporate Offices • P.O. Box 2300';
        $contactCityStateZip = $isCustom
            ? ($institution->mailing_city_state_zip ?: 'Cleveland, MS 38732')
            : 'Cleveland, OH 44166';

        $pdf = Pdf::loadView('frontend::user.transaction.receipt_pdf', compact(
            'transaction', 'user', 'logoBase64', 'siteTitle', 'primaryColor', 
            'regulatorBadge', 'contactAddress', 'contactCityStateZip'
        ));
        
        $filename = 'Receipt_' . $transaction->tnx . '.pdf';
        return $pdf->download($filename);
    }

    public function emailReceipt($tnx)
    {
        $transaction = Transaction::where('user_id', auth()->id())
            ->where('tnx', $tnx)
            ->firstOrFail();

        $user = auth()->user();
        
        // Resolve Institution Context
        $institution = $user->institution ?? \App\Models\Institution::getDefault();
        $isCustom = $institution && !$institution->is_default;

        $logoBase64 = null;
        if ($isCustom) {
            $instLogoPath = $institution->logo_path ? public_path('assets/' . $institution->logo_path) : null;
            if (!$instLogoPath || !file_exists($instLogoPath)) {
                $instLogoPath = public_path('assets/institutions/clevelandstatebank/images/logo.png');
            }
            if (file_exists($instLogoPath)) {
                try {
                    $logoData = file_get_contents($instLogoPath);
                    if ($logoData) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                    }
                } catch (\Exception $e) {
                    \Log::error("Receipt Email Local Logo Read Error: " . $e->getMessage());
                }
            }
        } else {
            $siteLogo = setting('site_logo', 'global');
            $logoPath = $siteLogo ? public_path('assets/' . $siteLogo) : null;
            if ($logoPath && file_exists($logoPath)) {
                try {
                    $logoData = file_get_contents($logoPath);
                    if ($logoData) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                    }
                } catch (\Exception $e) {
                    \Log::error("Receipt Email Local Logo Read Error: " . $e->getMessage());
                }
            }
            if (!$logoBase64) {
                try {
                    $fallbackPath = public_path('assets/global/images/6RR9UFs6kLq67BrPItMv.png');
                    if (file_exists($fallbackPath)) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($fallbackPath));
                    }
                } catch (\Exception $e) {
                    \Log::error("Receipt Email Local Fallback Logo Read Error: " . $e->getMessage());
                }
            }
        }

        $siteTitle = $institution ? $institution->name : (setting('site_title', 'global') ?? 'FrontField FCU');
        $primaryColor = $institution->primary_color ?? ($isCustom ? '#c8232c' : '#00549b');
        $regulatorBadge = $isCustom
            ? ('Member ' . ($institution->regulator_type ? strtoupper($institution->regulator_type) : 'FDIC') . ' • Equal Housing Lender')
            : 'Federally Insured by NCUA • Equal Housing Lender';
        $contactAddress = $isCustom
            ? ($institution->mailing_address ?: 'Corporate Offices • P.O. Box 278')
            : 'Corporate Offices • P.O. Box 2300';
        $contactCityStateZip = $isCustom
            ? ($institution->mailing_city_state_zip ?: 'Cleveland, MS 38732')
            : 'Cleveland, OH 44166';
        $contactPhone = $isCustom
            ? ($institution->support_phone ?: '662-843-9461')
            : (setting('site_phone', 'global') ?: '216 230 1837');

        $customDomain = $institution?->custom_domain;
        if ($customDomain && !str_ends_with(strtolower($customDomain), '.frontfieldcu.pro')) {
            $siteLink = 'https://' . ltrim($customDomain, 'https://');
        } elseif ($isCustom) {
            $siteLink = '#';
        } else {
            $siteLink = route('home');
        }

        $instLogo = $institution?->logo_path;
        if ($instLogo && !\Illuminate\Support\Str::startsWith($instLogo, ['assets/', 'storage/'])) {
            $instLogo = 'assets/' . $instLogo;
        }
        $siteLogoUrl = $instLogo ? asset($instLogo) : ($isCustom ? asset('assets/institutions/clevelandstatebank/images/logo.png') : (setting('site_logo', 'global') ? asset('assets/' . setting('site_logo', 'global')) : null));

        $pdf = Pdf::loadView('frontend::user.transaction.receipt_pdf', compact(
            'transaction', 'user', 'logoBase64', 'siteTitle', 'primaryColor', 
            'regulatorBadge', 'contactAddress', 'contactCityStateZip'
        ));
        
        $filename = 'Receipt_' . $transaction->tnx . '.pdf';

        $details = [
            'subject' => 'Transaction Receipt - ' . $transaction->tnx,
            'title' => 'Your Transaction Receipt',
            'salutation' => 'Hello ' . $user->full_name,
            'message_body' => 'Attached is the receipt for your transaction #' . $transaction->tnx . ' for ' . setting('currency_symbol', 'global') . number_format($transaction->amount, 2) . '.',
            'button_level' => 'View Activity',
            'button_link' => route('user.transactions'),
            'footer_status' => 1,
            'bottom_status' => 0,
            'primary_color' => $primaryColor,
            'regulator_type' => $regulatorBadge,
            'support_phone' => $contactPhone,
            'site_logo' => $siteLogoUrl,
            'site_title' => $siteTitle,
            'site_link' => $siteLink,
            'attachment' => [
                'data' => $pdf->output(),
                'filename' => $filename
            ]
        ];

        try {
            if (!\App\Services\BrevoMailService::sendDetails($user->email, $details)) {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\MailSend($details));
            }
            notify()->success('Receipt has been sent to your email.', 'Success');
        } catch (\Throwable $e) {
            \Log::warning("Receipt email warning: " . $e->getMessage());
            notify()->info('Receipt ready for download.', 'Receipt');
        }

        return back();
    }

    /**
     * Upload an expense receipt image/pdf and add member notes to a transaction.
     */
    public function saveReceiptAndNote(Request $request, $id)
    {
        Transaction::ensureReceiptColumns();

        $user = auth()->user();
        $txn = Transaction::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
        ]);

        if ($request->has('notes')) {
            $txn->member_notes = $request->input('notes');
        }

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
            $filename = 'RCP_' . $txn->id . '_' . \Illuminate\Support\Str::random(12) . '.' . $ext;
            
            $targetDir = public_path('assets/global/images/receipts');
            if (!\Illuminate\Support\Facades\File::isDirectory($targetDir)) {
                \Illuminate\Support\Facades\File::makeDirectory($targetDir, 0755, true, true);
            }
            $file->move($targetDir, $filename);
            $txn->receipt_attachment = 'assets/global/images/receipts/' . $filename;
        }

        $txn->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Receipt and notes updated successfully!',
            'receipt_url' => $txn->receipt_attachment ? asset($txn->receipt_attachment) : null,
            'notes' => $txn->member_notes,
        ]);
    }
}
