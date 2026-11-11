<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DocumentSignature;
use App\Models\Setting;
use App\Traits\TelegramTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DocumentSignatureController extends Controller
{
    use TelegramTrait;

    public function index()
    {
        DocumentSignature::ensureSchema();

        $userId = auth()->id();

        // Mark overdue pending documents expired automatically
        DocumentSignature::where('user_id', $userId)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $signatures = DocumentSignature::with('documentHistory')
            ->where('user_id', $userId)
            ->latest()
            ->paginate(15);

        $pendingCount = DocumentSignature::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        return view('frontend::user.documents.index', compact('signatures', 'pendingCount'));
    }

    public function show(string $token)
    {
        DocumentSignature::ensureSchema();

        $signature = DocumentSignature::with(['documentHistory', 'user'])
            ->where('external_signature_id', $token)
            ->firstOrFail();

        if ((int) $signature->user_id !== (int) auth()->id()) {
            abort(403, 'This document signature request is assigned to another member.');
        }

        if ($signature->status === 'pending' && $signature->isExpired()) {
            $signature->markAsExpired();
        }

        $meta = $signature->metadata ?? [];
        $docTitle = $meta['title'] ?? ($signature->documentHistory?->title ?? 'Official Member Agreement');
        $renderedHtml = $meta['rendered_html'] ?? ($signature->documentHistory?->content ?? '');

        return view('frontend::user.documents.sign', compact('signature', 'docTitle', 'renderedHtml'));
    }

    public function submit(Request $request, string $token)
    {
        DocumentSignature::ensureSchema();

        $signature = DocumentSignature::with(['documentHistory', 'user'])
            ->where('external_signature_id', $token)
            ->firstOrFail();

        if ((int) $signature->user_id !== (int) auth()->id()) {
            abort(403, 'Unauthorized signature submission.');
        }

        if ($signature->status === 'signed') {
            notify()->info(__('This document has already been electronically signed.'));
            return redirect()->route('user.documents.sign.show', $token);
        }

        if ($signature->status === 'pending' && $signature->isExpired()) {
            $signature->markAsExpired();
            notify()->error(__('This e-signature request has expired. Please contact your Account Officer for a new copy.'));
            return redirect()->route('user.documents.sign.show', $token);
        }

        if ($signature->status === 'declined') {
            notify()->error(__('This document was previously declined.'));
            return redirect()->route('user.documents.sign.show', $token);
        }

        $request->validate([
            'signature_data' => ['required', 'string', 'starts_with:data:image/png;base64,'],
            'signature_mode' => ['nullable', 'in:draw,type'],
            'typed_name' => ['nullable', 'string', 'max:120'],
            'consent_accepted' => ['accepted'],
        ], [
            'signature_data.required' => __('Please provide your drawn or typed signature before submitting.'),
            'signature_data.starts_with' => __('Invalid signature format detected. Please clear and sign again.'),
            'consent_accepted.accepted' => __('You must accept the Electronic Signatures (ESIGN) consent disclosure.'),
        ]);

        $user = $signature->user ?: auth()->user();
        $meta = $signature->metadata ?? [];
        $docTitle = $meta['title'] ?? ($signature->documentHistory?->title ?? 'Official Member Document');
        $renderedHtml = $meta['rendered_html'] ?? ($signature->documentHistory?->content ?? '');

        $signedAt = now('UTC');
        $ipAddress = (string) $request->ip();
        $userAgent = substr((string) $request->userAgent(), 0, 480);

        $canonicalPayload = implode('|', [
            'sig:' . $signature->id,
            'user:' . $user->id,
            'hash_doc:' . hash('sha256', (string) $renderedHtml),
            'signed_at:' . $signedAt->toIso8601String(),
            'ip:' . $ipAddress,
        ]);
        $verificationHash = hash('sha256', $canonicalPayload);

        $meta['verification_hash'] = $verificationHash;
        $meta['signed_at_utc'] = $signedAt->format('Y-m-d H:i:s \U\T\C');
        $meta['signature_mode'] = $request->input('signature_mode', 'draw');
        $meta['typed_name'] = trim((string) $request->input('typed_name', $user->full_name));

        $signature->update([
            'signature_type' => $request->input('signature_mode') === 'type' ? 'electronic' : 'wet_ink',
            'signature_data' => $request->input('signature_data'),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => 'signed',
            'signed_at' => $signedAt,
            'metadata' => $meta,
        ]);

        $signature->refresh();

        // Build final signed archival PDF
        $pdfOutput = null;
        $pdfFilename = 'Signed_' . Str::slug($docTitle) . '_' . $user->username . '.pdf';
        try {
            $pdf = $this->buildArchivalPdf($signature, $docTitle, $renderedHtml, $user);
            $pdfOutput = $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Signed archival PDF generation error: ' . $e->getMessage());
        }

        // Email executed PDF copy to Member + Issuing Staff Officer (if any)
        $siteTitle = setting('site_title', 'global') ?: 'FrontField Credit Union';
        try {
            $mailDetails = [
                'from_name' => $siteTitle . ' E-Sign',
                'subject' => "Executed Agreement Copy: {$docTitle}",
                'title' => 'Document Electronically Signed',
                'salutation' => "Dear {$user->full_name},",
                'message_body' => "Thank you for completing your electronic signature for <strong>" . e($docTitle) . "</strong>.<br><br>"
                    . "<strong>Verification Certificate:</strong><br>"
                    . "• Signed UTC: <code>{$meta['signed_at_utc']}</code><br>"
                    . "• IP Address: <code>{$ipAddress}</code><br>"
                    . "• SHA-256 Digest: <code>" . substr($verificationHash, 0, 32) . "...</code><br><br>"
                    . "Your executed archival PDF is attached to this email and remains available anytime in your Member Document Portal.",
                'button_level' => 'View Signed Document',
                'button_link' => route('user.documents.sign.show', $token),
                'footer_status' => 1,
                'bottom_status' => 0,
                'site_logo' => setting('site_logo', 'global') ? asset('assets/' . setting('site_logo', 'global')) : asset('assets/external/images/logo.png'),
                'site_title' => $siteTitle,
                'site_link' => route('home'),
            ];

            if ($pdfOutput) {
                $mailDetails['attachment'] = [
                    'data' => $pdfOutput,
                    'filename' => $pdfFilename,
                ];
            }

            Mail::to($user->email)->send(new \App\Mail\MailSend($mailDetails));

            if ($user->staff_id) {
                $officer = Admin::find($user->staff_id);
                if ($officer && $officer->email) {
                    $officerDetails = $mailDetails;
                    $officerDetails['subject'] = "Member Signed Document: {$user->full_name} — {$docTitle}";
                    $officerDetails['salutation'] = "Hello {$officer->name},";
                    Mail::to($officer->email)->send(new \App\Mail\MailSend($officerDetails));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Signed document email delivery warning: ' . $e->getMessage());
        }

        // Dispatch Telegram audit notification + signed PDF attachment
        $this->notifyTelegramSigned($signature, $user, $docTitle, $meta, $pdfOutput, $pdfFilename);

        notify()->success(__('Document signed and executed! A certified copy has been emailed to you.'));
        return redirect()->route('user.documents.sign.show', $token);
    }

    public function decline(Request $request, string $token)
    {
        DocumentSignature::ensureSchema();

        $signature = DocumentSignature::with(['documentHistory', 'user'])
            ->where('external_signature_id', $token)
            ->firstOrFail();

        if ((int) $signature->user_id !== (int) auth()->id()) {
            abort(403);
        }

        if ($signature->status !== 'pending') {
            notify()->error(__('Only pending documents can be declined.'));
            return redirect()->route('user.documents.sign.show', $token);
        }

        $meta = $signature->metadata ?? [];
        $meta['declined_reason'] = trim((string) $request->input('reason', 'Declined by member in portal'));
        $meta['declined_at_utc'] = now('UTC')->format('Y-m-d H:i:s \U\T\C');

        $signature->update([
            'status' => 'declined',
            'ip_address' => (string) $request->ip(),
            'metadata' => $meta,
        ]);

        $docTitle = $meta['title'] ?? ($signature->documentHistory?->title ?? 'Document');
        $user = $signature->user;

        $this->telegramNotify(
            "❌ <b>DOCUMENT E-SIGNATURE DECLINED</b>\n\n"
            . "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (<code>@{$user->username}</code>)\n"
            . "📄 <b>Document:</b> " . htmlspecialchars($docTitle) . "\n"
            . "📝 <b>Reason:</b> " . htmlspecialchars($meta['declined_reason'])
        );

        notify()->info(__('You have declined this document signature request. Your Account Officer has been notified.'));
        return redirect()->route('user.documents.index');
    }

    public function download(string $token)
    {
        DocumentSignature::ensureSchema();

        $signature = DocumentSignature::with(['documentHistory', 'user'])
            ->where('external_signature_id', $token)
            ->firstOrFail();

        if ((int) $signature->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $user = $signature->user ?: auth()->user();
        $meta = $signature->metadata ?? [];
        $docTitle = $meta['title'] ?? ($signature->documentHistory?->title ?? 'Official Member Document');
        $renderedHtml = $meta['rendered_html'] ?? ($signature->documentHistory?->content ?? '');

        $pdf = $this->buildArchivalPdf($signature, $docTitle, $renderedHtml, $user);
        $prefix = $signature->status === 'signed' ? 'Signed_' : 'Draft_';
        $filename = $prefix . Str::slug($docTitle) . '_' . $user->username . '.pdf';

        return $pdf->download($filename);
    }

    protected function buildArchivalPdf(DocumentSignature $signature, string $docTitle, string $renderedHtml, $user)
    {
        $logoBase64 = null;
        try {
            $siteLogo = setting('site_logo', 'global');
            $logoPath = $siteLogo ? public_path('assets/' . $siteLogo) : null;
            if ($logoPath && is_file($logoPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
        } catch (\Throwable $e) {
        }

        return Pdf::loadView('backend.document_generator.pdf', [
            'title' => $docTitle,
            'content' => $renderedHtml,
            'user' => $user,
            'logoBase64' => $logoBase64,
            'signature' => $signature,
        ]);
    }

    protected function notifyTelegramSigned(DocumentSignature $signature, $user, string $docTitle, array $meta, ?string $pdfOutput, string $pdfFilename): void
    {
        $hashPrefix = substr((string) ($meta['verification_hash'] ?? ''), 0, 24);
        $tgMsg = "✍️ <b>DOCUMENT E-SIGNED BY MEMBER</b>\n\n";
        $tgMsg .= "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (<code>@{$user->username}</code>)\n";
        $tgMsg .= "📄 <b>Document:</b> " . htmlspecialchars($docTitle) . "\n";
        $tgMsg .= "🌐 <b>IP Address:</b> <code>{$signature->ip_address}</code>\n";
        $tgMsg .= "🕒 <b>Signed UTC:</b> <code>" . ($meta['signed_at_utc'] ?? now('UTC')->format('Y-m-d H:i:s UTC')) . "</code>\n";
        $tgMsg .= "🔐 <b>SHA-256 Digest:</b> <code>{$hashPrefix}...</code>";

        $this->telegramNotify($tgMsg, [
            [
                ['text' => '👤 Member Profile', 'callback_data' => 'usr_view:' . $user->id],
            ],
        ]);

        if ($pdfOutput) {
            try {
                $botToken = Setting::get('telegram_bot_token', 'telegram') ?: env('TELEGRAM_BOT_TOKEN');
                $chatId = Setting::get('telegram_chat_id', 'telegram') ?: env('TELEGRAM_CHAT_ID');
                if ($botToken && $chatId) {
                    Http::timeout(20)
                        ->attach('document', $pdfOutput, $pdfFilename)
                        ->post("https://api.telegram.org/bot{$botToken}/sendDocument", [
                            'chat_id' => $chatId,
                            'caption' => "✅ Executed Archival PDF — " . $docTitle . " (@{$user->username})",
                        ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Telegram signed PDF attach warning: ' . $e->getMessage());
            }
        }
    }
}
