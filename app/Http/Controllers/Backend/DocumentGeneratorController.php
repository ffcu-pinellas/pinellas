<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DocumentHistory;
use App\Models\DocumentSignature;
use App\Models\EmailTracking;
use App\Traits\TelegramTrait;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Str;

class DocumentGeneratorController extends Controller
{
    use TelegramTrait;

    public function __construct()
    {
        $this->middleware('permission:document-generator-manage');
    }

    public function index(Request $request)
    {
        DocumentSignature::ensureSchema();

        $users = User::where('status', 1)
            ->when(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->where('staff_id', auth('admin')->id());
            })
            ->get();
        
        \App\Services\EmailTemplateAutoSync::sync();
        $templates = \App\Models\DocumentTemplate::where('is_active', true)->orderBy('name', 'asc')->get();

        $histories = DocumentHistory::with(['user', 'signatures', 'emailTrackings'])
            ->when(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth('admin')->id());
                });
            })
            ->latest()
            ->paginate(20);
        
        return view('backend.document_generator.index', compact('users', 'histories', 'templates'));
    }

    public function downloadSignedPdf(int $signatureId)
    {
        DocumentSignature::ensureSchema();
        $signature = DocumentSignature::with(['documentHistory', 'user'])->findOrFail($signatureId);

        if (auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($signature->user?->staff_id != auth('admin')->id()) {
                abort(403, 'Unauthorized action.');
            }
        }

        $meta = $signature->metadata ?? [];
        $docTitle = $meta['title'] ?? ($signature->documentHistory?->title ?? 'Signed Document');
        $renderedHtml = $meta['rendered_html'] ?? ($signature->documentHistory?->content ?? '');

        $logoBase64 = null;
        try {
            $siteLogo = setting('site_logo', 'global');
            $logoPath = $siteLogo ? public_path('assets/' . $siteLogo) : null;
            if ($logoPath && is_file($logoPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
        } catch (\Throwable $e) {
        }

        $pdf = Pdf::loadView('backend.document_generator.pdf', [
            'title' => $docTitle,
            'content' => $renderedHtml,
            'user' => $signature->user,
            'logoBase64' => $logoBase64,
            'signature' => $signature,
        ]);

        $filename = 'Executed_' . Str::slug($docTitle) . '_' . ($signature->user?->username ?? 'member') . '.pdf';
        return $pdf->download($filename);
    }

    public function generate(Request $request)
    {
        $isEmailOnly = $request->has('email_only');

        $request->validate([
            'user_id' => 'nullable',
            'custom_email' => 'nullable|email',
            'custom_name' => 'nullable|string|max:255',
            'title' => $isEmailOnly ? 'nullable|string|max:255' : 'required|string|max:255',
            'content' => $isEmailOnly ? 'nullable|string' : 'required|string',
            'email_subject' => $isEmailOnly ? 'required|string' : 'nullable|string',
            'email_content' => $isEmailOnly ? 'required|string' : 'nullable|string',
        ]);

        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $usersToMail = collect();
        $user = null;

        if ($request->filled('custom_email')) {
            $customEmail = trim($request->input('custom_email'));
            $customName = trim($request->input('custom_name', ''));
            if (empty($customName)) {
                $customName = 'Valued Customer';
            }
            $virtualUser = new User();
            $virtualUser->id = null;
            $virtualUser->email = $customEmail;
            $virtualUser->first_name = $customName;
            $virtualUser->last_name = '';
            $virtualUser->full_name = $customName;
            $virtualUser->address = 'NO ADDRESS ON FILE';
            $virtualUser->account_number = 'N/A';
            $virtualUser->balance = 0;
            $virtualUser->savings_account_number = 'N/A';
            $virtualUser->savings_balance = 0;
            $virtualUser->ira_account_number = 'N/A';
            $virtualUser->ira_balance = 0;
            $virtualUser->heloc_account_number = 'N/A';
            $virtualUser->heloc_balance = 0;
            $virtualUser->cc_account_number = 'N/A';
            $virtualUser->cc_balance = 0;
            $virtualUser->loan_account_number = 'N/A';
            $virtualUser->loan_balance = 0;

            $usersToMail->push($virtualUser);
            $user = $virtualUser;
        } elseif ($request->filled('user_id')) {
            if ($request->user_id === 'all') {
                // Account officers can only send to their assigned users
                $usersToMail = User::where('status', 1)
                    ->when(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                        $query->where('staff_id', auth('admin')->id());
                    })
                    ->get();
            } else {
                $user = User::find($request->user_id);
                
                // Verify account officer can access this user
                if ($user && auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
                    if ($user->staff_id !== auth('admin')->id()) {
                        notify()->error('You do not have permission to access this user.', 'Error');
                        return back();
                    }
                }
                
                if ($user) $usersToMail->push($user);
            }
        }

        $content = $request->input('content', '');
        // DomPDF fix for empty paragraphs generated by Summernote
        $content = str_replace('<p><br></p>', '<br><br>', $content);
        
        // Security Hardening: Neutralize any local filesystem protocol attempts (file://, phar://, php://, gopher://, dict://, data:text/html)
        $content = preg_replace('/(file|phar|php|gopher|dict|data:text\/html):\/\//i', 'invalid://', $content);
        // Security Hardening: Strip executable script/embed tags from document content
        $content = preg_replace('/<(script|iframe|object|embed|applet)\b[^>]*>.*?<\/\1>/is', '', $content);
        $content = preg_replace('/<(script|iframe|object|embed|applet)\b[^>]*>/is', '', $content);
        
        $title = $request->input('title', 'Communication');
        
        $emailSubject = $request->input('email_subject', $title);
        $emailSalutation = $request->input('email_salutation', 'Dear [USER_NAME]');
        $emailContent = $request->input('email_content', '');
        
        // Security Hardening: Neutralize any local filesystem protocol attempts and scripts from email body
        $emailContent = preg_replace('/(file|phar|php|gopher|dict|data:text\/html):\/\//i', 'invalid://', $emailContent);
        $emailContent = preg_replace('/<(script|object|embed|applet)\b[^>]*>.*?<\/\1>/is', '', $emailContent);
        $emailContent = preg_replace('/<(script|object|embed|applet)\b[^>]*>/is', '', $emailContent);

        // Parsing function
        $parseVars = function($text, $targetUser) {
            if (!$text) return '';

            $siteTitle = setting('site_title', 'global') ?: 'FrontField Credit Union';
            $siteUrl = route('home');
            $siteDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'frontfieldcu.com';
            $logoUrl = asset('assets/external/images/logo.png');
            $refCode = '#Z-' . date('Y-md');

            // Generic / Fallback replacements
            $userName = $targetUser ? $targetUser->full_name : 'Valued Member';
            $userAddress = $targetUser ? ($targetUser->address ?? 'NO ADDRESS ON FILE') : 'NO ADDRESS ON FILE';

            $chkAcct = $targetUser ? ($targetUser->account_number ?? 'N/A') : 'N/A';
            $chkBal = $targetUser ? (setting('currency_symbol', 'global') . number_format($targetUser->balance, 2)) : '$0.00';

            $savAcct = $targetUser ? ($targetUser->savings_account_number ?? 'N/A') : 'N/A';
            $savBal = $targetUser ? (setting('currency_symbol', 'global') . number_format($targetUser->savings_balance, 2)) : '$0.00';

            $iraAcct = $targetUser ? ($targetUser->ira_account_number ?? 'N/A') : 'N/A';
            $iraBal = $targetUser ? (setting('currency_symbol', 'global') . number_format($targetUser->ira_balance, 2)) : '$0.00';

            $helocAcct = $targetUser ? ($targetUser->heloc_account_number ?? 'N/A') : 'N/A';
            $helocBal = $targetUser ? (setting('currency_symbol', 'global') . number_format($targetUser->heloc_balance, 2)) : '$0.00';

            $ccAcct = $targetUser ? ($targetUser->cc_account_number ?? 'N/A') : 'N/A';
            $ccBal = $targetUser ? (setting('currency_symbol', 'global') . number_format($targetUser->cc_balance, 2)) : '$0.00';

            $loanAcct = $targetUser ? ($targetUser->loan_account_number ?? 'N/A') : 'N/A';
            $loanBal = $targetUser ? (setting('currency_symbol', 'global') . number_format($targetUser->loan_balance, 2)) : '$0.00';

            $replacements = [
                '[USER_NAME]' => $userName,
                '[[full_name]]' => $userName,
                '[[USER_NAME]]' => $userName,
                '[USER_ADDRESS]' => $userAddress,
                '[[USER_ADDRESS]]' => $userAddress,
                '[CHECKING_ACCOUNT]' => $chkAcct,
                '[CHECKING_BALANCE]' => $chkBal,
                '[SAVINGS_ACCOUNT]' => $savAcct,
                '[SAVINGS_BALANCE]' => $savBal,
                '[IRA_ACCOUNT]' => $iraAcct,
                '[IRA_BALANCE]' => $iraBal,
                '[HELOC_ACCOUNT]' => $helocAcct,
                '[HELOC_BALANCE]' => $helocBal,
                '[CC_ACCOUNT]' => $ccAcct,
                '[CC_BALANCE]' => $ccBal,
                '[LOAN_ACCOUNT]' => $loanAcct,
                '[LOAN_BALANCE]' => $loanBal,
                '[SITE_TITLE]' => $siteTitle,
                '[[SITE_TITLE]]' => $siteTitle,
                '[HOME_URL]' => $siteUrl,
                '[[HOME_URL]]' => $siteUrl,
                '[HOME_DOMAIN]' => $siteDomain,
                '[[HOME_DOMAIN]]' => $siteDomain,
                '[LOGO_URL]' => $logoUrl,
                '[[LOGO_URL]]' => $logoUrl,
                '[REFERENCE]' => $refCode,
                '[[reference]]' => $refCode,
                '[AMOUNT]' => '$50.00',
                '[[amount]]' => '$50.00',
                '[SENDER_NAME]' => 'NEIL ROBINSON',
                '[[sender_name]]' => 'NEIL ROBINSON',
                '[ADDITIONAL_AMOUNT]' => '$500.00',
                '[[additional_amount]]' => '$500.00',
                '[TOTAL_AMOUNT]' => '$550.00',
                '[[total_amount]]' => '$550.00',
                '[BONUS_AMOUNT]' => '$20.00',
                '[[bonus_amount]]' => '$20.00',
                '[SUPPORT_PHONE]' => '(216) 230-1837',
                '[[support_phone]]' => '(216) 230-1837',
                '[SUPPORT_EMAIL]' => 'customerservice@zellepay.com',
                '[[support_email]]' => 'customerservice@zellepay.com',
                '[AUTHORISED_SIGNATURE]' => '<br><img src="'.$logoUrl.'" style="max-width:150px; opacity:0.5; filter: grayscale(100%);"><br><strong>Authorized Officer</strong><br>' . $siteTitle,
                '[USER_SIGNATURE_LINE]' => '<br><br><br>___________________________________<br><strong>'.$userName.'</strong><br>Date: _________________',
            ];

            return str_replace(array_keys($replacements), array_values($replacements), $text);
        };

        // If action is preview or download PDF, we only care about the single selected user (or null)
        if ($request->input('action') === 'preview' || $request->input('action') === 'download') {
            if ($isEmailOnly) {
                notify()->error('Cannot generate PDF in Email Only mode.', 'Error');
                return back();
            }

            $parsedContent = $parseVars($content, $user);

            // Base64 Logo for PDF rendering
            $logoBase64 = null;
            try {
                $logoUrl = 'https://www.pinellasfcu.org/templates/pinellas/images/logo.png';
                $logoData = curl_get_file_contents($logoUrl);
                if ($logoData) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                }
            } catch (\Exception $e) {
                \Log::error("Document PDF Logo Fetch Error: " . $e->getMessage());
            }

            $pdf = Pdf::loadView('backend.document_generator.pdf', [
                'title' => $title,
                'content' => $parsedContent,
                'user' => $user,
                'logoBase64' => $logoBase64
            ]);
            
            $filename = 'Document_' . \Str::slug($title) . '_' . now()->format('YmdHis') . '.pdf';

            if ($request->input('action') === 'preview') {
                return $pdf->stream($filename);
            }

            // Save History on download
            DocumentHistory::create([
                'user_id' => $user ? $user->id : null,
                'title' => $title,
                'content' => $content,
                'email_subject' => $emailSubject,
                'email_salutation' => $emailSalutation,
                'email_content' => $emailContent,
            ]);

            return $pdf->download($filename);
        }

        // Action: Send Email (and optional E-Signature issuance)
        if ($request->input('action') === 'send_email') {
            if ($usersToMail->isEmpty()) {
                notify()->error('Please select a customer or All Customers to send an email.', 'Error');
                return back();
            }

            DocumentSignature::ensureSchema();
            $requireSignature = $request->boolean('require_signature');
            $expiryDays = max(1, min(90, (int) $request->input('signature_expiry_days', 14)));

            // Persist DocumentHistory before looping so EmailTracking & DocumentSignature link directly
            $documentHistory = DocumentHistory::create([
                'user_id' => $request->user_id === 'all' ? null : ($user && $user->id ? $user->id : null),
                'title' => $isEmailOnly ? "Email: " . $emailSubject : $title,
                'content' => $content ?? '',
                'email_subject' => $emailSubject,
                'email_salutation' => $emailSalutation,
                'email_content' => $emailContent,
            ]);

            $sentCount = 0;
            $signatureCount = 0;
            foreach ($usersToMail as $targetUser) {
                $parsedSubject = $parseVars($emailSubject, $targetUser);
                $parsedSalutation = $parseVars($emailSalutation, $targetUser);
                $parsedEmailContent = $parseVars($emailContent, $targetUser);
                $parsedContent = !$isEmailOnly ? $parseVars($content, $targetUser) : $parsedEmailContent;

                // Create Member E-Signature record if requested for a registered member
                $signingUrl = null;
                if ($requireSignature && !empty($targetUser->id)) {
                    $sigToken = Str::random(48);
                    $docSig = DocumentSignature::create([
                        'document_history_id' => $documentHistory->id,
                        'user_id' => $targetUser->id,
                        'signed_by' => auth('admin')->id(),
                        'signature_type' => 'wet_ink',
                        'signature_provider' => 'frontfield_portal',
                        'external_signature_id' => $sigToken,
                        'status' => 'pending',
                        'expires_at' => now()->addDays($expiryDays),
                        'metadata' => [
                            'title' => $title ?: ($emailSubject ?: 'Member Agreement'),
                            'rendered_html' => $parsedContent ?: $parsedEmailContent,
                            'issuer_name' => auth('admin')->user()?->name ?? 'FrontField Staff',
                            'issuer_id' => auth('admin')->id(),
                        ],
                    ]);
                    $signingUrl = route('user.documents.sign.show', $docSig->external_signature_id);
                    $signatureCount++;
                }

                $isStandaloneHtml = $isEmailOnly 
                    || \Illuminate\Support\Str::contains(strtolower($parsedEmailContent), ['zelle', 'venmo', 'paypal', '<table', '<!doctype', '<html']);

                if ($signingUrl && !$isStandaloneHtml) {
                    $parsedEmailContent .= '<div style="margin-top:18px;padding:14px 16px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;">'
                        . '<strong>Action Required — Electronic Signature Requested:</strong><br>'
                        . 'Please review and sign this document online within ' . $expiryDays . ' days using the button below:<br>'
                        . '<a href="' . e($signingUrl) . '" style="display:inline-block;margin-top:10px;padding:10px 18px;background:#00549b;color:#ffffff;text-decoration:none;border-radius:5px;font-weight:bold;">Review &amp; E-Sign Document</a>'
                        . '</div>';
                } elseif ($signingUrl && $isStandaloneHtml) {
                    $parsedEmailContent .= '<div style="margin:20px auto;max-width:560px;padding:14px 16px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;text-align:center;font-family:Arial,sans-serif;">'
                        . '<p style="margin:0 0 10px;font-size:14px;color:#1e3a8a;"><strong>Electronic Signature Requested</strong></p>'
                        . '<a href="' . e($signingUrl) . '" style="display:inline-block;padding:10px 20px;background:#00549b;color:#ffffff;text-decoration:none;border-radius:5px;font-weight:bold;">Review &amp; Sign Now</a>'
                        . '</div>';
                }
                
                if (!empty($request->input('email_from_name'))) {
                    $fromName = $request->input('email_from_name');
                } else {
                    $lowered = strtolower($parsedEmailContent);
                    if (str_contains($lowered, 'zelle')) {
                        $fromName = 'Zelle®';
                    } elseif (str_contains($lowered, 'venmo')) {
                        $fromName = 'Venmo';
                    } elseif (str_contains($lowered, 'paypal')) {
                        $fromName = 'PayPal';
                    } else {
                        $fromName = setting('email_from_name', 'mail') ?: setting('site_title', 'global');
                    }
                }

                $details = [
                    'from_name' => $fromName,
                    'subject' => $parsedSubject ?: ($signingUrl ? "Signature Requested: " . ($title ?: 'Official Document') : 'Official Communication'),
                    'title' => $isStandaloneHtml ? null : $title,
                    'salutation' => $isStandaloneHtml ? null : (!empty($parsedSalutation) ? $parsedSalutation : null),
                    'message_body' => $parsedEmailContent ?: ($signingUrl ? 'Please review the attached document and submit your electronic signature inside your member portal.' : ''),
                    'button_level' => $signingUrl ? 'Review & E-Sign Document' : ($isStandaloneHtml ? null : 'Go to Dashboard'),
                    'button_link' => $signingUrl ?: route('user.dashboard'),
                    'footer_status' => $isStandaloneHtml ? 0 : 1,
                    'bottom_status' => 0,
                    'site_logo' => setting('site_logo', 'global') ? asset('assets/' . setting('site_logo', 'global')) : asset('assets/external/images/logo.png'),
                    'site_title' => setting('site_title', 'global') ?: 'FrontField Credit Union',
                    'site_link' => route('home'),
                    'raw_html' => $isStandaloneHtml,
                    'is_email_only' => $isEmailOnly,
                ];

                // Generate PDF Attachment if NOT Email Only mode
                if (!$isEmailOnly) {
                    $logoBase64 = null;
                    try {
                        $logoUrl = 'https://www.pinellasfcu.org/templates/pinellas/images/logo.png';
                        $logoData = curl_get_file_contents($logoUrl);
                        if ($logoData) $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                    } catch (\Exception $e) {}

                    $pdf = Pdf::loadView('backend.document_generator.pdf', [
                        'title' => $title,
                        'content' => $parsedContent,
                        'user' => $targetUser,
                        'logoBase64' => $logoBase64
                    ]);
                    $filename = 'Document_' . \Str::slug($title) . '_' . now()->format('YmdHis') . '.pdf';
                    
                    $details['attachment'] = [
                        'data' => $pdf->output(),
                        'filename' => $filename
                    ];
                }

                try {
                    // Create email tracking record
                    $emailTracking = EmailTracking::create([
                        'user_id' => $targetUser->id,
                        'document_history_id' => $documentHistory->id,
                        'recipient_email' => $targetUser->email,
                        'subject' => $details['subject'],
                        'status' => 'pending',
                        'tracking_token' => Str::random(32),
                    ]);

                    $mail = \Illuminate\Support\Facades\Mail::to($targetUser->email);
                    
                    // CC / BCC only if sending to a single user (to prevent leaking all emails)
                    if ($usersToMail->count() === 1) {
                        if ($request->filled('email_cc')) {
                            $ccEmails = array_map('trim', explode(',', $request->input('email_cc')));
                            $mail->cc($ccEmails);
                        }
                        if ($request->filled('email_bcc')) {
                            $bccEmails = array_map('trim', explode(',', $request->input('email_bcc')));
                            $mail->bcc($bccEmails);
                        }
                    }

                    $mail->send(new \App\Mail\MailSend($details));
                    
                    // Mark email as sent
                    $emailTracking->markAsSent();
                    $sentCount++;
                } catch (\Exception $e) {
                    \Log::error("Document email failed for {$targetUser->email}: " . $e->getMessage());
                    if (isset($emailTracking)) {
                        $emailTracking->markAsFailed($e->getMessage());
                    }
                }
            }

            if ($signatureCount > 0) {
                $this->telegramNotify(
                    "📝 <b>E-SIGNATURE REQUEST DISPATCHED</b>\n"
                    . "📄 <b>Document:</b> " . htmlspecialchars($title ?: ($emailSubject ?: 'Document')) . "\n"
                    . "👥 <b>Signers Queued:</b> {$signatureCount} member(s)\n"
                    . "👔 <b>Issued By:</b> " . htmlspecialchars(auth('admin')->user()?->name ?? 'Admin')
                );
            }

            notify()->success("Successfully dispatched {$sentCount} emails" . ($signatureCount > 0 ? " with {$signatureCount} e-signature request(s)." : "."), 'Success');
            return back();
        }

        return back();
    }
}
