<?php

namespace App\Traits;

use App\Events\NotificationEvent;
use App\Mail\MailSend;
use App\Models\EmailTemplate;
use App\Support\MailAsset;
use App\Models\Notification;
use App\Models\PushNotificationTemplate;
use App\Models\SmsTemplate;
use Exception;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

trait NotifyTrait
{
    use SmsTrait, TelegramTrait;
    
    /**
     * Standardize and format shortcodes for all notifications
     */
    protected function formatShortcodes($shortcodes)
    {
        if (!is_array($shortcodes)) return $shortcodes;

        $currencySymbol = setting('currency_symbol', '$');
        
        foreach ($shortcodes as $key => $value) {
            // Identify amount-related fields
            if (str_contains($key, 'amount') || str_contains($key, 'charge') || str_contains($key, 'fee')) {
                if (is_numeric($value)) {
                    $formattedValue = $currencySymbol . number_format($value, 2);
                    $shortcodes[$key] = $formattedValue; // Replace original with formatted
                    
                    // Also add a specific formatted version if not already present
                    $formattedKey = str_replace('[[', '[[formatted_', $key);
                    if ($formattedKey === $key) { // If it didn't have brackets already
                         $formattedKey = 'formatted_' . $key;
                    }
                    $shortcodes[$formattedKey] = $formattedValue;
                }
            } elseif (is_string($value) && !str_contains($key, 'url') && !str_contains($key, 'link') && !str_contains($key, 'email') && !str_contains($key, 'site_title')) {
                $shortcodes[$key] = $this->scrubTelegramText($value);
            }
        }

        return $shortcodes;
    }

    /**
     * Map common alternate shortcodes used in DB email templates (e.g. [[txn]] vs [[tnx]]).
     */
    protected function expandMailShortcodeAliases(?array $shortcodes): array
    {
        $shortcodes = $shortcodes ?? [];

        if (isset($shortcodes['[[tnx]]']) && ! isset($shortcodes['[[txn]]'])) {
            $shortcodes['[[txn]]'] = $shortcodes['[[tnx]]'];
        }
        if (isset($shortcodes['[[txn]]']) && ! isset($shortcodes['[[tnx]]'])) {
            $shortcodes['[[tnx]]'] = $shortcodes['[[txn]]'];
        }

        $reason = $shortcodes['[[message]]'] ?? $shortcodes['[[reason]]'] ?? $shortcodes['[[action_message]]'] ?? null;
        $reason = trim((string) $reason);
        if ($reason === '') {
            $reason = 'No additional details were provided.';
        }
        foreach (['[[message]]', '[[reason]]', '[[action_message]]'] as $key) {
            if (! array_key_exists($key, $shortcodes) || trim((string) $shortcodes[$key]) === '') {
                $shortcodes[$key] = $reason;
            }
        }

        return $shortcodes;
    }

    /**
     * Replace any shortcodes still present after substitution (avoids raw [[txn]] in member-facing mail).
     */
    protected function stripUnresolvedMailShortcodes(?string $content): string
    {
        if ($content === null || $content === '') {
            return (string) $content;
        }

        return preg_replace('/\[\[[^\]]+\]\]/', '—', $content);
    }

    /**
     * Universal plain text sanitizer to scrub any administrator or Telegram mentions
     * into clean, simple banking terminology ("Approved", "Declined", "Declined and refunded").
     */
    public static function scrubText(?string $text): string
    {
        if ($text === null || $text === '') {
            return (string) $text;
        }

        // 1. Exact phrase replacements for specialized internal / Telegram terms
        $phraseReplacements = [
            'Manual Balance Adjustment via Telegram'          => 'Manual Balance Adjustment',
            'Telegram Direct Action'                          => 'Automated Dividend Credit',
            'Telegram One-Click Batch'                        => 'Scheduled Dividend Batch',
            'Telegram /dividend run'                          => 'Scheduled Dividend Distribution',
            'Telegram Super-Admin'                            => 'Operations Review',
            'Telegram Reg CC ($225 Immediate)'                => 'Reg CC Immediate Funds Availability',
            'Administrative review: Transaction rejected'     => 'Declined',
            'Administrative rejection via Telegram'           => 'Declined',
            'Administrative rejection'                        => 'Declined',
            'declined and refunded by Operations'             => 'Declined and refunded',
            'Transaction declined and refunded by Operations' => 'Declined and refunded',
            'declined by Operations'                          => 'Declined',
            'Transaction declined by Operations'              => 'Declined',
        ];

        foreach ($phraseReplacements as $needle => $replacement) {
            $text = str_ireplace($needle, $replacement, $text);
        }

        // 2. Generic patterns for approvals -> "Approved"
        // Catches variations like "Approved via Telegram...", "Approved by authorized administrator(s)", "Approved by admin", "Approved by the administrator", etc.
        $text = preg_replace(
            '/\b(?:Transaction\s+)?Approved\s+(?:via\s+Telegram(?:\s+(?:Inline\s+Action|Bot|command))?|by\s+(?:(?:the|an?|authorized|system|senior|bank|credit\s+union)\s+)*(?:admin(?:istrator)?s?|administration|operations|staff))\b/i',
            'Approved',
            $text
        );

        // 3. Generic patterns for rejections with refund -> "Declined and refunded"
        $text = preg_replace(
            '/\b(?:Transaction\s+)?(?:Rejected|Declined)\s+and\s+refunded(?:\s+(?:via\s+Telegram(?:\s+(?:Inline\s+Action|Bot|command))?|by\s+(?:(?:the|an?|authorized|system|senior|bank|credit\s+union)\s+)*(?:admin(?:istrator)?s?|administration|operations|staff)))?\b/i',
            'Declined and refunded',
            $text
        );

        // 4. Generic patterns for rejections without refund -> "Declined"
        $text = preg_replace(
            '/\b(?:Transaction\s+)?(?:Rejected|Declined)\s+(?:via\s+Telegram(?:\s+(?:Inline\s+Action|Bot|command))?|by\s+(?:(?:the|an?|authorized|system|senior|bank|credit\s+union)\s+)*(?:admin(?:istrator)?s?|administration|operations|staff))\b/i',
            'Declined',
            $text
        );

        // If the entire text is a rejection variant like "Rejected", "Rejected.", "declined by Operations", etc.
        if (preg_match('/^(?:Transaction\s+)?(?:rejected|declined)(?:\s+(?:by\s+[a-z\s]+|via\s+[a-z\s]+))?[\.\s]*$/i', trim($text))) {
            $text = 'Declined';
        }

        // If the entire text is an approval variant like "Approved", "Approved.", "Approved by authorized administrator", etc.
        if (preg_match('/^(?:Transaction\s+)?approved(?:\s+(?:by\s+[a-z\s]+|via\s+[a-z\s]+))?[\.\s]*$/i', trim($text))) {
            $text = 'Approved';
        }

        // 5. Scrub leftover Telegram mentions
        $text = preg_replace('/\b(?:via|on|from)\s+Telegram\b/i', '', $text);
        $text = preg_replace('/\bTelegram\s+Bot\b/i', 'Automated System', $text);
        $text = preg_replace('/\bTelegram\b/i', 'Operations', $text);

        // 6. Scrub any leftover "by authorized administrator" or "by admin"
        $text = preg_replace('/\bby\s+(?:(?:the|an?|authorized|system)\s+)?admin(?:istrator)?s?\b/i', '', $text);

        // 7. Ensure NO "administrator" words leak: convert all administrator/admin variations to clean, simple words
        $text = preg_replace('/\b(?:authorized|system)\s+admin(?:istrator)?s?\b/i', 'Operations', $text);
        $text = preg_replace('/\badministrative\s+review\b/i', 'review', $text);
        $text = preg_replace('/\badministrative\s+action\b/i', 'action', $text);
        $text = preg_replace('/\badministrative\s+decision\b/i', 'decision', $text);
        $text = preg_replace('/\badministrative(?:ly)?\b/i', 'Operations', $text);
        $text = preg_replace('/\badministrator(?:s|\'s)?\b/i', 'Staff', $text);
        $text = preg_replace('/\badministration\b/i', 'Operations', $text);
        $text = preg_replace('/\badmin(?:s|\'s)?\b/i', 'Staff', $text);

        // 8. Collapse whitespace and fix spacing before punctuation
        $text = preg_replace('/\s+([.,;:!?])/', '$1', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text);

        return trim($text);
    }

    /**
     * Render a clean, institutional-grade banking receipt HTML table.
     */
    protected function renderStructuredReceiptHtml(array $rows, string $amount, string $statusText, string $transferType, string $brandColor = '#00549b'): string
    {
        $html = '<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px 20px; margin: 15px 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Arial, sans-serif;">';
        
        // Status header & amount
        $html .= '<div style="text-align: center; padding-bottom: 18px; border-bottom: 1px solid #e2e8f0;">';
        $html .= '<div style="display: inline-block; background-color: #ecfdf5; color: #047857; font-weight: 700; font-size: 12px; padding: 5px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">';
        $html .= '&#10003; ' . htmlspecialchars($statusText) . '</div>';
        $html .= '<div style="font-size: 32px; font-weight: 800; color: #0f172a; margin: 4px 0;">' . htmlspecialchars($amount) . '</div>';
        $html .= '<div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">' . htmlspecialchars($transferType) . ' Confirmation</div>';
        $html .= '</div>';
        
        // Summary Key-Value Rows
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13.5px;">';
        foreach ($rows as $label => $val) {
            if ($val === null || $val === '' || $val === 'N/A') continue;
            $html .= '<tr style="border-bottom: 1px solid #f1f5f9;">';
            $html .= '<td style="padding: 10px 0; color: #64748b; font-weight: 500; vertical-align: top;">' . htmlspecialchars($label) . '</td>';
            $html .= '<td style="padding: 10px 0; text-align: right; color: #0f172a; font-weight: 600; vertical-align: top;">' . htmlspecialchars($val) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        
        // Reassurance footer
        $html .= '<div style="margin-top: 18px; padding: 12px 14px; background-color: #ffffff; border-left: 3px solid ' . htmlspecialchars($brandColor) . '; border-radius: 4px; font-size: 12px; color: #475569; line-height: 1.5;">';
        $html .= 'This transaction has been authorized, processed, and recorded on the banking ledger. Funds have been transmitted via secure payment networks.';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Sanitize plain text to scrub any user-facing Telegram mentions.
     */
    protected function scrubTelegramText(?string $text): string
    {
        return self::scrubText($text);
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    protected function sanitizeMailTemplateDetails(array $details): array
    {
        foreach (['subject', 'title', 'salutation', 'message_body', 'footer_body', 'bottom_title', 'bottom_body', 'button_link'] as $key) {
            if (isset($details[$key]) && is_string($details[$key])) {
                $details[$key] = $this->stripUnresolvedMailShortcodes($details[$key]);
            }
        }

        // Automatic scrubber to sanitize user-facing email details and eliminate Telegram references
        foreach (['subject', 'title', 'salutation', 'message_body', 'footer_body', 'bottom_title', 'bottom_body'] as $key) {
            if (isset($details[$key]) && is_string($details[$key])) {
                $details[$key] = $this->scrubTelegramText($details[$key]);
            }
        }

        return $details;
    }

    // ============================= mail template helper ===================================================
    protected function mailNotify($email, $code, $shortcodes = null)
    {
        $shortcodes = $this->expandMailShortcodeAliases($this->formatShortcodes($shortcodes));
        try {
            $user = \App\Models\User::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first()
                ?? \App\Models\User::where('email', $email)->first();
            $institution = $user?->institution
                ?? (auth()->check() ? auth()->user()->institution : null)
                ?? (app()->bound('institution') ? app('institution') : null)
                ?? (!empty($shortcodes['[[site_title]]']) && $shortcodes['[[site_title]]'] !== setting('site_title', 'global') ? \App\Models\Institution::where('name', $shortcodes['[[site_title]]'])->first() : null)
                ?? \App\Models\Institution::getDefault();
            $isCustom = $institution && !$institution->is_default;
            $isCsb = ($institution && in_array(strtolower($institution->slug ?? ''), ['clevelandstatebank', 'clevelandstate', 'csb']))
                || ($institution && strtolower($institution->short_name ?? '') === 'csb')
                || (isset($shortcodes['[[site_title]]']) && stripos($shortcodes['[[site_title]]'], 'Cleveland') !== false)
                || $isCustom;

            if ($user && empty($user->institution_id) && $isCsb && $institution) {
                try {
                    $user->update(['institution_id' => $institution->id]);
                } catch (\Throwable $e) {}
            }

            $siteTitle = $institution ? $institution->name : ($isCsb ? 'Cleveland State Bank' : (setting('site_title', 'global') ?: 'FrontField Credit Union'));
            $primaryColor = $institution?->primary_color ?: ($isCsb ? '#c8232c' : '#00549b');
            $regulator = $isCsb
                ? ($institution?->regulator_badge ?: 'Member FDIC')
                : ($institution?->regulator_badge ?: 'Federally Insured by NCUA');
            $supportPhone = $institution?->support_phone ?: ($isCsb ? '216-230-1837' : (setting('site_phone', 'global') ?: setting('support_phone', 'global')));
            $supportEmail = $institution?->support_email ?: ($isCsb ? 'support@clevelandstatebank.com' : setting('support_email', 'global'));
            $fromName = $institution?->mail_from_name ?: ($isCsb ? 'Cleveland State Bank' : ($institution?->name ?: setting('site_title', 'global')));
            $fromAddress = $institution?->mail_from_address ?: ($isCsb ? 'support@clevelandstatebank.com' : setting('support_email', 'global'));

            // Dynamic Logo Resolution from $institution->logo_path
            $siteLogo = $institution?->logo_path;
            if ($siteLogo) {
                if (!Str::startsWith($siteLogo, ['assets/', 'storage/'])) {
                    $siteLogo = file_exists(public_path('storage/' . $siteLogo)) ? 'storage/' . $siteLogo : 'assets/' . $siteLogo;
                }
            }
            if (!$siteLogo || !file_exists(public_path($siteLogo))) {
                $siteLogo = $isCsb
                    ? 'assets/institutions/clevelandstatebank/images/logo.png'
                    : (setting('site_logo', 'global') ?: 'assets/external/images/logo.png');
                if ($siteLogo && !Str::startsWith($siteLogo, ['assets/', 'storage/'])) {
                    $siteLogo = 'assets/' . $siteLogo;
                }
            }

            // User-facing Clean Site Link
            $customDomain = $institution?->custom_domain;
            if ($customDomain && !str_ends_with(strtolower($customDomain), '.frontfieldcu.pro')) {
                $siteLink = 'https://' . ltrim($customDomain, 'https://');
            } elseif ($isCsb) {
                $siteLink = '#';
            } else {
                $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'frontfieldcu.pro';
                if (!str_ends_with(strtolower($appHost), '.frontfieldcu.pro') || $appHost === 'frontfieldcu.pro') {
                    $siteLink = rtrim((string) config('app.url'), '/') . '/';
                } else {
                    $siteLink = 'https://frontfieldcu.pro/';
                }
            }

            $template = EmailTemplate::where('status', true)->where('code', $code)->first();
            if ($template) {
                $find = array_keys($shortcodes);
                $replace = array_values($shortcodes);

                // Add Standard Shortcodes if not present
                if (!in_array('[[status]]', $find)) {
                    $find[] = '[[status]]';
                    $replace[] = $shortcodes['[[status]]'] ?? $shortcodes['status'] ?? '';
                }
                if (!in_array('[[site_title]]', $find)) {
                    $find[] = '[[site_title]]';
                    $replace[] = $siteTitle;
                }
                if (!in_array('[[site_url]]', $find)) {
                    $find[] = '[[site_url]]';
                    $replace[] = $siteLink;
                }

                $banner = $template->banner;
                if ($banner && !Str::startsWith($banner, ['assets/', 'storage/'])) {
                    $banner = 'assets/'.$banner;
                }

                $details = [
                    'subject' => str_replace($find, $replace, $template->subject),
                    'banner' => $banner ? MailAsset::absolute($banner) : null,
                    'title' => str_replace($find, $replace, $template->title),
                    'salutation' => str_replace($find, $replace, $template->salutation),
                    'message_body' => $shortcodes['[[message_body]]'] ?? $shortcodes['message_body'] ?? str_replace($find, $replace, $template->message_body),
                    'button_level' => $shortcodes['[[button_level]]'] ?? $shortcodes['button_level'] ?? $template->button_level,
                    'button_link' => $shortcodes['[[button_link]]'] ?? $shortcodes['button_link'] ?? str_replace($find, $replace, $template->button_link),
                    'footer_status' => $template->footer_status,
                    'footer_body' => str_replace($find, $replace, $template->footer_body),
                    'bottom_status' => $template->bottom_status,
                    'bottom_title' => str_replace($find, $replace, $template->bottom_title) ?? '',
                    'bottom_body' => str_replace($find, $replace, $template->bottom_body) ?? '',

                    'site_logo' => MailAsset::absolute($siteLogo),
                    'site_logo_path' => $siteLogo ? public_path($siteLogo) : null,
                    'site_title' => $siteTitle,
                    'site_link' => $siteLink,
                    'primary_color' => $primaryColor,
                    'regulator_type' => $regulator,
                    'support_phone' => $supportPhone,
                    'support_email' => $supportEmail,
                    'from_name' => $fromName,
                    'from_email' => $fromAddress,
                ];

                // Check Deposit Structured Receipt Integration
                if ($code === 'user_manual_deposit_request' || $code === 'deposit_approved' || $code === 'remote_deposit_approved' || $code === 'remote_deposit_regcc_hold') {
                    $statusRaw = $shortcodes['[[status]]'] ?? 'approved';
                    $statusUpper = strtoupper($statusRaw);
                    $depAmt = $shortcodes['[[amount]]'] ?? ($shortcodes['[[deposit_amount]]'] ?? '$0.00');
                    $tnxRef = $shortcodes['[[txn]]'] ?? ($shortcodes['[[tnx]]'] ?? 'N/A');
                    $toAcc = $shortcodes['[[account_name]]'] ?? ($shortcodes['[[to_account]]'] ?? 'Personal Checking');
                    $immAmt = $shortcodes['[[immediate_amount]]'] ?? null;
                    $heldAmt = $shortcodes['[[held_amount]]'] ?? null;
                    $relDate = $shortcodes['[[release_date]]'] ?? now()->addWeekdays(5)->format('M d, Y');

                    if (str_contains($statusUpper, 'REG CC') || str_contains($statusUpper, 'PARTIALLY') || !empty($immAmt)) {
                        $details['subject'] = 'Regulation CC Notice: Mobile Check Deposit Partial Availability - Ref #' . $tnxRef;
                        $details['title'] = 'Funds Availability Notice';
                        $receiptRows = [
                            'Status' => 'Partially Cleared (Reg CC Hold)',
                            'Total Check Amount' => is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt,
                            'Immediate Availability' => $immAmt ?: '$225.00',
                            'Held Balance (5 Business Days)' => $heldAmt ?: 'Remainder',
                            'Scheduled Availability Date' => $relDate,
                            'Deposited To' => $toAcc,
                            'Deposit Reference' => $tnxRef,
                            'Hold Authority' => 'Federal Regulation CC (12 CFR Part 229)',
                        ];
                        $details['message_body'] = $this->renderStructuredReceiptHtml($receiptRows, is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt, 'Partial Availability (Reg CC Hold)', 'Mobile Check Deposit', $primaryColor);
                        $details['message_body'] .= '<div style="margin-top:16px; padding:12px 14px; background-color:#eff6ff; border-left:3px solid #3b82f6; border-radius:4px; font-size:12.5px; color:#1e40af; line-height:1.5;"><strong>Regulation CC Disclosure:</strong> In accordance with federal availability schedules, $225.00 has been credited to your available balance immediately. The remaining balance is held for standard verification and will become available on ' . htmlspecialchars($relDate) . '.</div>';
                    } elseif (str_contains($statusUpper, 'HOLD')) {
                        $details['subject'] = 'Notice of Delayed Availability: Check Placed on Hold - Ref #' . $tnxRef;
                        $details['title'] = 'Funds Availability Notice';
                        $receiptRows = [
                            'Status' => 'Placed on 5-Business-Day Hold',
                            'Total Check Amount' => is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt,
                            'Immediate Availability' => '$0.00',
                            'Held Balance' => is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt,
                            'Available Date' => $relDate,
                            'Deposited To' => $toAcc,
                            'Deposit Reference' => $tnxRef,
                            'Reason' => 'Regulation CC Standard Verification Hold',
                        ];
                        $details['message_body'] = $this->renderStructuredReceiptHtml($receiptRows, is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt, 'Funds on Hold (5 Business Days)', 'Mobile Check Deposit', $primaryColor);
                        $details['message_body'] .= '<div style="margin-top:16px; padding:12px 14px; background-color:#fef3c7; border-left:3px solid #f59e0b; border-radius:4px; font-size:12.5px; color:#92400e; line-height:1.5;"><strong>Notice of Delayed Availability:</strong> We have accepted your mobile check deposit of ' . htmlspecialchars(is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt) . '. In accordance with standard banking schedules, these funds are subject to a 5-business-day verification hold and will become fully available on ' . htmlspecialchars($relDate) . '.</div>';
                    } elseif (str_contains($statusUpper, 'REJECT') || str_contains($statusUpper, 'DECLIN') || str_contains($statusUpper, 'FAILED')) {
                        $details['subject'] = 'Action Required: Mobile Check Deposit Retake - Ref #' . $tnxRef;
                        $details['title'] = 'Check Deposit Update';
                        $receiptRows = [
                            'Status' => 'Action Required (Photo Retake)',
                            'Check Amount' => is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt,
                            'Deposited To' => $toAcc,
                            'Deposit Reference' => $tnxRef,
                            'Guidance' => $shortcodes['[[message]]'] ?? 'Please capture clear front and back photos with proper endorsement and resubmit.',
                        ];
                        $details['message_body'] = $this->renderStructuredReceiptHtml($receiptRows, is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt, 'Retake Required', 'Mobile Check Deposit', $primaryColor);
                    } else {
                        // Full approval
                        $details['subject'] = 'Deposit Confirmation: ' . (is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt) . ' Credited - Ref #' . $tnxRef;
                        $details['title'] = 'Check Deposit Cleared';
                        $receiptRows = [
                            'Status' => 'Approved & Available',
                            'Deposit Amount' => is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt,
                            'Credited To' => $toAcc,
                            'Confirmation #' => $tnxRef,
                            'Funds Availability' => 'Immediate Access',
                            'Settlement Date' => now()->format('M d, Y h:i A'),
                        ];
                        $details['message_body'] = $this->renderStructuredReceiptHtml($receiptRows, is_numeric($depAmt) ? ('$' . number_format((float)$depAmt, 2)) : $depAmt, 'Funds Cleared', 'Mobile Check Deposit', $primaryColor);
                    }
                }

                // Guard against single-word bodies in any template
                if (in_array(strtolower(trim(strip_tags($details['message_body'] ?? ''))), ['approved', 'approve', 'success', 'completed', 'declined', 'rejected'])) {
                    $details['message_body'] = 'Your transaction (Ref #' . ($shortcodes['[[txn]]'] ?? ($shortcodes['[[tnx]]'] ?? 'N/A')) . ') for ' . ($shortcodes['[[amount]]'] ?? ($shortcodes['[[deposit_amount]]'] ?? '$0.00')) . ' has been processed: ' . ucfirst(trim(strip_tags($details['message_body']))) . '.';
                }

                // When under a custom institution (e.g. Cleveland State Bank), scrub any hardcoded legacy brand names from DB templates
                if ($isCsb) {
                    $details['subject'] = str_ireplace(['FrontField Credit Union', 'FrontField FCU', 'Frontfield', 'FFCU'], $siteTitle, $details['subject']);
                    $details['title'] = str_ireplace(['FrontField Credit Union', 'FrontField FCU', 'Frontfield', 'FFCU'], $siteTitle, $details['title']);
                    $details['salutation'] = str_ireplace(['FrontField Credit Union', 'FrontField FCU', 'Frontfield', 'FFCU'], $siteTitle, $details['salutation']);
                    $details['message_body'] = str_ireplace(['FrontField Credit Union', 'FrontField FCU', 'Frontfield', 'FFCU'], $siteTitle, $details['message_body']);
                    $details['footer_body'] = str_ireplace(['FrontField Credit Union', 'FrontField FCU', 'Frontfield', 'FFCU'], $siteTitle, $details['footer_body']);
                    $details['footer_body'] = str_ireplace('NCUA', 'FDIC', $details['footer_body']);
                } else {
                    $details['footer_body'] = str_ireplace('FDIC', 'NCUA', $details['footer_body']);
                    $details['message_body'] = str_ireplace('FDIC', 'NCUA', $details['message_body']);
                }

                $details = $this->sanitizeMailTemplateDetails($details);

                if ($code == 'email_verification') {
                    $senderAddress = config('mail.from.address') ?: setting('email_from_address', 'mail') ?: 'devonbrown0122@gmail.com';
                    $msg = (new MailMessage)
                        ->from($senderAddress, $fromName)
                        ->replyTo($supportEmail, $fromName)
                        ->subject($details['subject'])
                        ->view('backend.mail.user-mail-send', ['details' => $details]);
                    return $msg;
                }

            // Attempt instant dispatch via Resend REST API (HTTPS port 443 - zero SMTP port blocking)
            try {
                if (\App\Services\ResendMailService::sendDetails($email, $details)) {
                    return true;
                }
            } catch (\Throwable $re) {
                \Log::warning("ResendMailService attempt error: " . $re->getMessage());
            }

            // Secondary fallback via Brevo REST API
            try {
                if (\App\Services\BrevoMailService::sendDetails($email, $details)) {
                    return true;
                }
            } catch (\Throwable $be) {
                \Log::warning("BrevoMailService attempt error: " . $be->getMessage());
            }

            // Tertiary fallback via standard Laravel mailer
            try {
                \Mail::to($email)->send(new \App\Mail\MailSend($details));
                return true;
            } catch (\Throwable $me) {
                \Log::warning("Standard Mail sending fallback failed for $email: " . $me->getMessage());
            }

            \Log::info("Mail notification processed/logged for $email (Subject: {$details['subject']}).");
            return false;
        } else {
            \Log::warning("Email template with code '$code' not found in database.");
            
            // Standard Fallback for ANY missing template to prevent failure
            if (!$siteLogo || !file_exists(public_path($siteLogo))) {
                if ($isCsb) {
                    $siteLogo = 'assets/institutions/clevelandstatebank/images/logo.png';
                } else {
                    $siteLogo = setting('site_logo', 'global');
                    if ($siteLogo && !Str::startsWith($siteLogo, ['assets/', 'storage/'])) {
                        $siteLogo = 'assets/'.$siteLogo;
                    }
                    if (!$siteLogo || Str::contains($siteLogo, 'fav') || !file_exists(public_path($siteLogo))) {
                        $siteLogo = 'assets/external/images/logo.png';
                    }
                }
            }

            // Prioritize manual inputs from the Admin Modal (Subject and Email Details)
            $manualSubject = $shortcodes['[[subject]]'] ?? null;
            $manualMessage = $shortcodes['[[message]]'] ?? null;
            $rawMsg = $manualMessage ?: ($shortcodes['[[action]]'] ?? '');

            // Fallback for single-word / truncated messages
            if (empty($rawMsg) || in_array(strtolower(trim($rawMsg)), ['approved', 'approve', 'success', 'completed'])) {
                $rawMsg = 'Your transaction (Ref #' . ($shortcodes['[[tnx]]'] ?? ($shortcodes['[[txn]]'] ?? 'N/A')) . ') for ' . ($shortcodes['[[amount]]'] ?? '$0.00') . ' has been approved and successfully credited to your account.';
            }

            $details = [
                'subject' => $manualSubject ?: 'Account Notification - ' . $siteTitle,
                'message_body' => $rawMsg,
                'site_logo' => MailAsset::absolute($siteLogo),
                'site_logo_path' => $siteLogo ? public_path($siteLogo) : null,
                'site_link' => $siteLink,
                'site_title' => $siteTitle,
                'primary_color' => $primaryColor,
                'regulator_type' => $regulator,
                'support_phone' => $supportPhone,
                'support_email' => $supportEmail,
                'from_name' => $fromName,
                'from_email' => $fromAddress,
                'title' => ($manualSubject && $manualMessage) ? 'Official Notification' : 'Account Alert',
                'salutation' => 'Hello ' . ($shortcodes['[[full_name]]'] ?? 'Member'),
                'footer_body' => $siteTitle,
                'footer_status' => 1,
                'banner' => null,
                'button_level' => null,
                'button_link' => null,
                'bottom_status' => 0,
                'bottom_title' => '',
                'bottom_body' => '',
            ];

            // Specific Fallbacks for commonly missing templates
            $tnxRef = $shortcodes['[[tnx]]'] ?? ($shortcodes['[[txn]]'] ?? 'TRX');
            $amtVal = $shortcodes['[[amount]]'] ?? '$0.00';
            $fromAcc = $shortcodes['[[from_account]]'] ?? 'Checking Account';
            $toAcc = $shortcodes['[[to_account]]'] ?? 'Designated Beneficiary';
            $destBank = $shortcodes['[[bank_name]]'] ?? 'Federal Reserve Clearing Network';
            $routing = $shortcodes['[[routing_number]]'] ?? 'N/A';
            $feeVal = $shortcodes['[[charge]]'] ?? '$0.00';
            $totVal = $shortcodes['[[total_amount]]'] ?? $amtVal;
            $memoVal = $shortcodes['[[memo]]'] ?? 'Electronic Funds Transfer';
            $dateVal = $shortcodes['[[date]]'] ?? now()->format('M d, Y h:i A');

            if ($code === 'wire_transfer' || $code === 'wire_transfer_request') {
                $details['subject'] = 'Wire Transfer Order Submitted - Ref #' . $tnxRef;
                $details['title'] = 'Wire Transfer Submitted';
                $details['message_body'] = $this->renderStructuredReceiptHtml([
                    'Transfer Type' => 'Federal Reserve Wire Network',
                    'Amount' => $amtVal,
                    'Wire Fee' => $feeVal,
                    'Total Deducted' => $totVal,
                    'From Account' => $fromAcc,
                    'Beneficiary' => $toAcc,
                    'Receiving Bank' => $destBank,
                    'Routing / SWIFT' => $routing,
                    'Reference #' => $tnxRef,
                    'Submitted At' => $dateVal,
                ], $amtVal, 'Pending Final Settlement', 'Wire Transfer', $primaryColor);
            } elseif ($code === 'wire_transfer_approved') {
                $details['subject'] = 'Wire Transfer Dispatched & Settlement Complete - Ref #' . $tnxRef;
                $details['title'] = 'Wire Transfer Completed';
                $details['message_body'] = $this->renderStructuredReceiptHtml([
                    'Status' => 'Dispatched & Settled',
                    'Amount Transferred' => $amtVal,
                    'Wire Fee' => $feeVal,
                    'Total Debited' => $totVal,
                    'From Account' => $fromAcc,
                    'Beneficiary' => $toAcc,
                    'Beneficiary Institution' => $destBank,
                    'Routing / SWIFT' => $routing,
                    'Federal Reference #' => $tnxRef,
                    'Settlement Date' => $dateVal,
                ], $amtVal, 'Completed', 'Fedwire Transfer', $primaryColor);
            } elseif ($code === 'wire_transfer_rejected') {
                $details['subject'] = 'Wire Transfer Order Cancelled & Refunded - Ref #' . $tnxRef;
                $details['title'] = 'Wire Order Cancelled';
                $details['message_body'] = 'Your wire transfer request (Ref #' . $tnxRef . ') could not be completed and has been cancelled. Funds (' . $totVal . ') have been safely returned to your available balance.<br><br><strong>Reason:</strong> ' . ($shortcodes['[[message]]'] ?? 'Unable to verify recipient routing details with receiving bank.');
            } elseif ($code === 'external_transfer_approved' || $code === 'fund_transfer_user' || $code === 'fund_transfer_approved') {
                $details['subject'] = 'Transfer Approved & Dispatched - Ref #' . $tnxRef;
                $details['title'] = 'Transfer Successfully Settled';
                $details['message_body'] = $this->renderStructuredReceiptHtml([
                    'Status' => 'Completed / Dispatched',
                    'Amount Sent' => $amtVal,
                    'Transfer Fee' => $feeVal,
                    'Total Deducted' => $totVal,
                    'From Account' => $fromAcc,
                    'Recipient / Payee' => $toAcc,
                    'Receiving Bank' => $destBank,
                    'Routing Transit' => $routing,
                    'Confirmation Number' => $tnxRef,
                    'Date & Time' => $dateVal,
                    'Memo' => $memoVal,
                ], $amtVal, 'Approved & Settled', 'ACH / External Transfer', $primaryColor);
            } elseif ($code === 'member_transfer_approved') {
                $details['subject'] = 'Member Transfer Approved & Delivered - Ref #' . $tnxRef;
                $details['title'] = 'Transfer Successfully Completed';
                $details['message_body'] = $this->renderStructuredReceiptHtml([
                    'Status' => 'Credited to Recipient',
                    'Amount Transferred' => $amtVal,
                    'Fee' => '$0.00 (Free Member Transfer)',
                    'From Account' => $fromAcc,
                    'Recipient Account' => $toAcc,
                    'Confirmation Number' => $tnxRef,
                    'Processed Date' => $dateVal,
                    'Memo / Note' => $memoVal,
                ], $amtVal, 'Delivered', 'Member-to-Member Transfer', $primaryColor);
            } elseif ($code === 'remote_deposit_approved') {
                $checkAmt = $shortcodes['[[check_amount]]'] ?? $amtVal;
                $details['subject'] = 'Mobile Check Deposit Cleared & Credited - Ref #' . $tnxRef;
                $details['title'] = 'Check Deposit Cleared';
                $details['message_body'] = $this->renderStructuredReceiptHtml([
                    'Status' => 'Cleared & Available',
                    'Check Amount' => $checkAmt,
                    'Deposited To' => $toAcc,
                    'Confirmation #' => $tnxRef,
                    'Funds Availability' => 'Immediate Access',
                    'Settlement Date' => $dateVal,
                ], $checkAmt, 'Funds Cleared', 'Mobile Check Deposit', $primaryColor);
            } elseif ($code === 'remote_deposit_submitted') {
                $details['subject'] = 'Check Deposit Received - ' . $siteTitle;
                $details['title'] = 'Check Received';
                $details['message_body'] = 'We have received your mobile check deposit of ' . ($shortcodes['[[amount]]'] ?? $amtVal) . ' to your ' . ($shortcodes['[[account_name]]'] ?? $toAcc) . '. Our team is now reviewing the deposit. You will receive another notification once it is processed and completed or if more information is needed.<br><br><strong>Deposit Reference:</strong> ' . $tnxRef;
            } elseif ($code === 'user_manual_deposit_request' || $code === 'deposit_approved') {
                $statusUpper = strtoupper($shortcodes['[[status]]'] ?? 'APPROVED');
                $isApproved = str_contains($statusUpper, 'APPROV') || str_contains($statusUpper, 'COMPLET') || str_contains($statusUpper, 'SUCCESS');
                $depAmt = $shortcodes['[[deposit_amount]]'] ?? ($shortcodes['[[amount]]'] ?? $amtVal);
                $details['subject'] = ($isApproved ? 'Check Deposit Approved & Credited - Ref #' : 'Check Deposit Update - Ref #') . $tnxRef;
                $details['title'] = $isApproved ? 'Check Deposit Credited' : 'Deposit Update';
                $details['message_body'] = $this->renderStructuredReceiptHtml([
                    'Status' => $isApproved ? 'Approved & Funds Available' : 'Under Review',
                    'Check Amount' => $depAmt,
                    'Deposited To' => $shortcodes['[[account_name]]'] ?? ($shortcodes['[[to_account]]'] ?? 'Personal Checking'),
                    'Confirmation Number' => $tnxRef,
                    'Method' => $shortcodes['[[gateway_name]]'] ?? 'Mobile Check Deposit',
                    'Processing Date' => $dateVal,
                ], $depAmt, $isApproved ? 'Funds Available' : 'In Review', 'Check Deposit', $primaryColor);
            } elseif ($code === 'card_status_update') {
                $details['subject'] = 'Security Alert: Card Status Changed';
                $details['title'] = 'Card Update';
                $details['message_body'] = $shortcodes['[[message]]'] ?? 'A status change was detected for your card ending in ' . ($shortcodes['[[card_number]]'] ?? 'XXXX') . '.';
            } elseif ($code === 'card_security_update') {
                $details['subject'] = 'Security Alert: Card Security Action';
                $details['title'] = 'Security Update';
                $details['message_body'] = $shortcodes['[[message]]'] ?? 'A security action (' . ($shortcodes['[[action]]'] ?? 'Update') . ') was performed on your card ending in ' . ($shortcodes['[[card_number]]'] ?? 'XXXX') . '.';
            }

            $details = $this->sanitizeMailTemplateDetails($details);

            // Attempt instant dispatch via Resend REST API (HTTPS port 443 - zero SMTP port blocking)
            try {
                if (\App\Services\ResendMailService::sendDetails($email, $details)) {
                    return true;
                }
            } catch (\Throwable $re) {
                \Log::warning("ResendMailService attempt error: " . $re->getMessage());
            }

            // Secondary fallback via Brevo REST API
            try {
                if (\App\Services\BrevoMailService::sendDetails($email, $details)) {
                    return true;
                }
            } catch (\Throwable $be) {
                \Log::warning("BrevoMailService attempt error: " . $be->getMessage());
            }

            // Tertiary fallback via standard Laravel mailer
            try {
                \Mail::to($email)->send(new \App\Mail\MailSend($details));
                return true;
            } catch (\Throwable $me) {
                \Log::warning("Standard Mail sending fallback failed for $email: " . $me->getMessage());
            }

            \Log::info("Mail notification fallback logged for $email (Subject: {$details['subject']}).");
            return false;
        }
    } catch (\Throwable $e) {
        \Log::warning("NotifyTrait mailNotify error: " . $e->getMessage());
        return false;
    }
}

    // ============================= push notification template helper ===================================================
    protected function pushNotify($code, $shortcodes, $action, $userId, $for = 'User')
    {
        $shortcodes = $this->formatShortcodes($shortcodes);
        try {
            $template = PushNotificationTemplate::where('status', true)->where('for', ucfirst($for))->where('code', $code)->first();

            if ($template) {
                $user = \App\Models\User::find($userId);
                $find = array_keys($shortcodes);
                $replace = array_values($shortcodes);
                $data = [
                    'icon' => $template->icon,
                    'user_id' => $userId,
                    'for' => Str::snake($template->for),
                    'title' => $this->scrubTelegramText(str_replace($find, $replace, $template->title)),
                    'notice' => strip_tags($this->scrubTelegramText(str_replace($find, $replace, $template->message_body))),
                    'action_url' => $action,
                ];

                Notification::create($data);

                // Internal Socket Event (Pusher)
                try {
                    $broadcastDriver = config('broadcasting.default');
                    $pusherKey = config('broadcasting.connections.pusher.key');
                    if ($broadcastDriver === 'pusher' && !empty($pusherKey)) {
                        $uID = $template->for == 'Admin' ? '' : $userId;
                        event(new NotificationEvent($template->for, $data, $uID));
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Pusher broadcast skipped: " . $e->getMessage());
                }

                // Native Push via FCM
                if ($for === 'User' && $user && $user->fcm_token) {
                    $this->sendFcmPush($user->fcm_token, $data['title'], $data['notice'], $action);
                } elseif ($for === 'Admin') {
                    // Send to specific admin if userId is provided, else potentially broadcast to all admins with tokens
                    if ($userId) {
                        $admin = \App\Models\Admin::find($userId);
                        if ($admin && $admin->fcm_token) {
                            $this->sendFcmPush($admin->fcm_token, $data['title'], $data['notice'], $action);
                        }
                    } else {
                        // Optional: Broadcast to admin who has a token (capped at 1 to prevent thread latency)
                        $adminsWithTokens = \App\Models\Admin::whereNotNull('fcm_token')->limit(1)->get();
                        foreach ($adminsWithTokens as $admin) {
                            $this->sendFcmPush($admin->fcm_token, $data['title'], $data['notice'], $action);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error("Push Notification Error: " . $e->getMessage());
        }
    }

    /**
     * Send Native FCM Push (V1)
     */
    protected function sendFcmPush($token, $title, $body, $action = null)
    {
        \Log::info("FCM: Attempting to send push to token: " . substr($token, 0, 10) . "...");
        $accessToken = $this->getFcmAccessToken();
        if (!$accessToken) {
            \Log::error("FCM: Failed to get access token");
            return;
        }

        $path = base_path('fcm_config/fcm_service_account.json');
        if (!file_exists($path)) {
            \Log::error("FCM: Service Account JSON missing at base path: $path");
            return;
        }
        $config = json_decode(file_get_contents($path), true);
        $projectId = $config['project_id'];

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $fields = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'action_url' => (string)$action,
                    'click_action' => 'FCM_PLUGIN_ACTIVITY',
                ],
                'android' => [
                   'notification' => [
                       'click_action' => 'FCM_PLUGIN_ACTIVITY',
                       'sound' => 'default'
                   ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default'
                        ]
                    ]
                ]
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        \Log::info("FCM V1 Send Result (HTTP $httpCode): " . $result);
    }

    /**
     * Generate OAuth 2.0 Access Token for FCM V1
     */
    protected function getFcmAccessToken()
    {
        $path = base_path('fcm_config/fcm_service_account.json');
        if (!file_exists($path)) {
            \Log::error("FCM Service Account JSON not found at BASE: $path");
            return null;
        }

        $config = json_decode(file_get_contents($path), true);
        if (!$config || !isset($config['private_key']) || !isset($config['client_email'])) {
            \Log::error("FCM: Invalid service account JSON structure.");
            return null;
        }

        $now = time();
        // Offset iat by 60 seconds to account for potential clock drift between server and Google
        $iat = $now - 60; 
        $exp = $now + 3600;

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => $config['client_email'],
            // Official FCM specific scope
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            // Using v4 token endpoint for broader account compatibility
            'aud' => 'https://www.googleapis.com/oauth2/v4/token',
            'iat' => $iat,
            'exp' => $exp,
        ];

        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $base64UrlHeader = $this->base64UrlEncode(json_encode($header, $jsonOptions));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload, $jsonOptions));

        \Log::debug("FCM JWT V4 Debug - Time: " . time() . " | IAT: " . $iat . " | EXP: " . $exp);

        // Aggressive Private Key Scrubbing
        $privateKeyContent = $config['private_key'];
        // Remove literal \n text
        $privateKeyContent = str_replace(['\\n', '\n', '\r'], "\n", $privateKeyContent);
        // Extract content between BEGIN and END tags if they exist (standardizing PEM)
        if (preg_match('/(-----BEGIN PRIVATE KEY-----.*?-----END PRIVATE KEY-----)/s', $privateKeyContent, $matches)) {
            $privateKeyContent = $matches[1];
        }
        $privateKeyContent = trim($privateKeyContent);

        $privateKey = openssl_get_privatekey($privateKeyContent);
        if (!$privateKey) {
            \Log::error("FCM: Failed to parse private key. PEM format may be corrupted.");
            return null;
        }

        $signature = '';
        if (!openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            \Log::error("FCM JWT Signing failed.");
            return null;
        }
        
        if (is_resource($privateKey) || (PHP_VERSION_ID >= 80000 && $privateKey instanceof \OpenSSLAsymmetricKey)) {
            openssl_free_key($privateKey);
        }

        $base64UrlSignature = $this->base64UrlEncode($signature);
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v4/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]));
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resData = json_decode($result, true);
        if (isset($resData['access_token'])) {
            return $resData['access_token'];
        }

        \Log::error("FCM: OAuth Token Exchange Failed (HTTP $httpCode): " . $result . " | ISS: " . $config['client_email']);
        return null;
    }

    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    // ============================= sms notification template helper ===================================================
    protected function smsNotify($code, $shortcodes, $phone)
    {
        $shortcodes = $this->formatShortcodes($shortcodes);
        if (! config('sms.default') && ! $phone) {
            return false;
        }

        try {
            $template = SmsTemplate::where('status', true)->where('code', $code)->first();
            if ($template) {
                $find = array_keys($shortcodes);
                $replace = array_values($shortcodes);

                $message = [
                    'message_body' => $this->scrubTelegramText(str_replace($find, $replace, $template->message_body)),
                ];
                self::sendSms($phone, $message);
            }

        } catch (\Throwable $e) {
            return false;
        }

    }

    /**
     * Dispatch important user activity alerts to the assigned Account Officer
     */
    public function notifyAssignedAccountOfficer($user, string $subject, string $message, array $shortcodes = []): void
    {
        try {
            if (!$user) return;
            $officer = $user->staff ?? null;
            $officerEmail = $officer?->email;
            $officerName = $officer?->name ?: 'Account Officer Desk';

            if (!$officerEmail) {
                $officerEmail = setting('support_email', 'global') ?: setting('site_email', 'global');
            }

            if (!$officerEmail) return;

            $inst = app()->bound('institution') ? app('institution') : ($user->institution ?? null);
            $isCsb = ($inst && ($inst->slug === 'clevelandstatebank' || !$inst->is_default))
                || (function_exists('request') && request() && str_contains(request()->getHost(), 'cleveland'));
            $instName = $inst ? $inst->name : ($isCsb ? 'Cleveland State Bank' : (setting('site_title') ?? 'FrontField Credit Union'));

            $mergedShortcodes = array_merge([
                '[[full_name]]' => $user->full_name ?? $user->username ?? 'Member',
                '[[member_name]]' => $user->full_name ?? $user->username ?? 'Member',
                '[[officer_name]]' => $officerName,
                '[[account_number]]' => '****' . substr($user->account_number ?? '0000', -4),
                '[[action]]' => $message,
                '[[message_body]]' => $message,
                '[[date]]' => now()->format('M d, Y h:i A'),
                '[[site_title]]' => $instName,
            ], $shortcodes);

            $this->mailNotify($officerEmail, 'account_officer_activity', $mergedShortcodes, [
                'subject' => '[' . $instName . '] Account Officer Alert: ' . $subject,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('notifyAssignedAccountOfficer warning: ' . $e->getMessage());
        }
    }
}
