<?php

namespace App\Jobs;

use App\Models\EmailTracking;
use App\Models\User;
use App\Models\DocumentHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RetryFailedEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $failedEmails = EmailTracking::failed()->where('retry_count', '<', 3)->get();

        foreach ($failedEmails as $emailTracking) {
            if (!$emailTracking->canRetry()) {
                continue;
            }

            try {
                $user = User::find($emailTracking->user_id);
                $documentHistory = $emailTracking->documentHistory;

                if (!$user || !$documentHistory) {
                    Log::warning("Cannot retry email: missing user or document history", [
                        'email_tracking_id' => $emailTracking->id,
                        'user_id' => $emailTracking->user_id,
                        'document_history_id' => $emailTracking->document_history_id,
                    ]);
                    continue;
                }

                // Reconstruct email details
                $details = [
                    'subject' => $emailTracking->subject,
                    'title' => $documentHistory->title,
                    'salutation' => $documentHistory->email_salutation ?? 'Dear [USER_NAME]',
                    'message_body' => $documentHistory->email_content,
                    'button_level' => 'Go to Dashboard',
                    'button_link' => route('user.dashboard'),
                    'footer_status' => 1,
                    'bottom_status' => 0,
                    'site_logo' => setting('site_logo', 'global') ? asset('assets/' . setting('site_logo', 'global')) : null,
                    'site_title' => setting('site_title', 'global'),
                    'site_link' => route('home'),
                ];

                // Add PDF attachment if available
                if (!str_starts_with($documentHistory->title, 'Email:')) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('backend.document_generator.pdf', [
                        'title' => $documentHistory->title,
                        'content' => $documentHistory->content,
                        'user' => $user,
                        'logoBase64' => null,
                    ]);
                    $filename = 'Document_' . \Str::slug($documentHistory->title) . '_retry.pdf';
                    $details['attachment'] = [
                        'data' => $pdf->output(),
                        'filename' => $filename
                    ];
                }

                Mail::to($user->email)->send(new \App\Mail\MailSend($details));
                
                // Update tracking
                $emailTracking->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'retry_count' => $emailTracking->retry_count + 1,
                    'error_message' => null,
                ]);

                Log::info("Email retry successful", [
                    'email_tracking_id' => $emailTracking->id,
                    'user_email' => $user->email,
                    'retry_count' => $emailTracking->retry_count,
                ]);

            } catch (\Exception $e) {
                Log::error("Email retry failed", [
                    'email_tracking_id' => $emailTracking->id,
                    'error' => $e->getMessage(),
                    'retry_count' => $emailTracking->retry_count,
                ]);

                $emailTracking->markAsFailed($e->getMessage());
            }
        }
    }
}
