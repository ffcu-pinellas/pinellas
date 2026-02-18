<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledTransfer;
use App\Services\TransferService;
use App\Models\User;
use Carbon\Carbon;

class ProcessScheduledTransfers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfer:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process due scheduled transfers';

    /**
     * Execute the console command.
     */
    public function handle(TransferService $transferService)
    {
        $transfers = ScheduledTransfer::where('status', 'active')
            ->where(function($q) {
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', now());
            })->get();

        foreach ($transfers as $transfer) {
            try {
                $user = User::find($transfer->user_id);
                if (!$user) continue;

                $data = [
                    'amount' => $transfer->amount,
                    'bank_id' => $transfer->meta_data['bank_id'] ?? 0,
                    'beneficiary_id' => $transfer->meta_data['beneficiary_id'] ?? null,
                    'manual_data' => $transfer->meta_data['manual_data'] ?? [],
                    'purpose' => ($transfer->meta_data['purpose'] ?? '') . ' (Scheduled)',
                ];

                // Validate and Process
                $transferService->validate($user, $data, $transfer->wallet_type);
                $transferService->process($user, $data, $transfer->wallet_type);

                // Update Next Run
                if ($transfer->frequency == 'once') {
                    $transfer->status = 'completed';
                } else {
                    $next = Carbon::now();
                    if ($transfer->frequency == 'daily') $next->addDay();
                    if ($transfer->frequency == 'weekly') $next->addWeek();
                    if ($transfer->frequency == 'monthly') $next->addMonth();
                    $transfer->next_run_at = $next;
                }
                $transfer->save();
                
                $this->info("Processed transfer {$transfer->id}");

            } catch (\Exception $e) {
                $this->error("Failed transfer {$transfer->id}: " . $e->getMessage());
                // Optionally mark as failed or log error
                // $transfer->status = 'failed';
                // $transfer->save();
            }
        }
    }
}
