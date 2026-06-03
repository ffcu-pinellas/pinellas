<?php

namespace App\Listeners;

use App\Mail\WelcomeMemberMail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;

        try {
            if ($user && $user->email) {
                Mail::to($user->email)->send(new WelcomeMemberMail($user));
            }
        } catch (\Exception $e) {
            Log::error("Failed to send welcome email to {$user->email}: " . $e->getMessage());
        }
    }
}
