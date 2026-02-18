<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledTransfer;
use App\Services\TransferService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        $this->info('Starting scheduled transfer processing...');
        
        $transfers = ScheduledTransfer::where('status', 'active')
            ->where('next_run_at', '<=', now())
            ->get();

        $this->info('Found ' . $transfers->count() . ' transfers due.');

        foreach ($transfers as $transfer) {
            try {
                $user = $transfer->user;
                $input = $transfer->meta_data;
                $input['amount'] = $transfer->amount;
                $input['bank_id'] = $input['bank_id'] ?? 0; // Ensure bank_id exists

                // Process Transfer
                $transferService->process($user, $input, $transfer->wallet_type);
                
                $this->info("Processed transfer ID: {$transfer->id}");

                // Update Next Run Date
                if ($transfer->frequency == 'once') {
                    $transfer->status = 'completed';
                    $transfer->save();
                } else {
                    $nextDate = Carbon::parse($transfer->next_run_at);
                    
                    switch ($transfer->frequency) {
                        case 'daily':
                            $nextDate->addDay();
                            break;
                        case 'weekly':
                            $nextDate->addWeek();
                            break;
                        case 'monthly':
                            $nextDate->addMonth();
                            break;
                    }
                    
                    $transfer->next_run_at = $nextDate;
                    $transfer->save();
                }

            } catch (\Exception $e) {
                Log::error("Scheduled Transfer Failed (ID: {$transfer->id}): " . $e->getMessage());
                $this->error("Failed ID: {$transfer->id} - " . $e->getMessage());
                
                // Optional: Disable after X failures or just leave active to retry? 
                // For robustness, maybe status = 'failed' if it's a critical error, 
                // but for "insufficient funds" we might want to retry next time?
                // Let's mark 'failed' for now to prevent infinite loops if data is bad.
                if (str_contains($e->getMessage(), 'Insufficient balance')) {
                    // Maybe retry next time? Leave active but don't advance date? 
                    // Or advance date to try again next cycle? 
                    // Standard bank: usually fails and charges a fee. 
                    // Here we will just log and maybe mark failed to stop it.
                    $transfer->status = 'failed';
                    $transfer->save();
                }
            }
        }

        $this->info('Scheduled transfer processing completed.');
    }
}
