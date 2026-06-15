<?php

namespace App\Http\Controllers\Backend;

use App\Enums\TransferType;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserWallet;
use App\Mail\FundTransferRejectedUserMail;
use App\Traits\NotifyTrait;
use App\Traits\RewardTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\EmailTracking;
use App\Mail\ExternalRecipientNotification;
use App\Models\DocumentTemplate;

class FundTransferController extends Controller
{
    use NotifyTrait,RewardTrait;

    public function __construct()
    {
        $this->middleware('permission:pending-transfers|officer-transfer-manage', ['only' => ['pending']]);
        $this->middleware('permission:rejected-transfers|officer-transfer-manage', ['only' => ['rejected']]);
        $this->middleware('permission:all-transfers|officer-transfer-manage', ['only' => ['all']]);
        $this->middleware('permission:allied-transfers|officer-transfer-manage', ['only' => ['allied']]);
        $this->middleware('permission:other-bank-transfers|officer-transfer-manage', ['only' => ['other']]);
        $this->middleware('permission:wire-transfer|officer-transfer-manage', ['only' => ['wire']]);
        $this->middleware('permission:fund-transfer-approval|officer-transfer-manage', ['only' => ['details', 'actionNow']]);
    }

    public function pending(Request $request)
    {
        $perPage = $request->perPage ?? 15;
        $search = $request->search ?? null;
        $type = $request->type ?? 'all';
        $status = $request->status ?? 'all';

        $lists = Transaction::with('user')
            ->pending()
            ->fundTransfar()
            ->status($status)
            ->search($search)
            ->transfertype($type)
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->when(in_array($request->sort_field, ['created_at', 'tnx', 'final_amount', 'type', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->when($request->sort_field == 'sender', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->orderBy('username', request('sort_dir'));
                });
            })
            ->when(! request()->has('sort_field'), function ($query) {
                $query->latest();
            })
            ->paginate($perPage);

        $statusForFrontend = 'Pending';

        return view('backend.fund-transfer.index', compact('lists', 'statusForFrontend'));
    }

    public function rejected(Request $request)
    {

        $perPage = $request->perPage ?? 15;
        $search = $request->search ?? null;
        $type = $request->type ?? 'all';
        $status = $request->status ?? 'all';

        $lists = Transaction::with('user')
            ->rejected()
            ->fundTransfar()
            ->status($status)
            ->search($search)
            ->transfertype($type)
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->when(in_array($request->sort_field, ['created_at', 'tnx', 'final_amount', 'type', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->when($request->sort_field == 'sender', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->orderBy('username', request('sort_dir'));
                });
            })
            ->when(! request()->has('sort_field'), function ($query) {
                $query->latest();
            })
            ->paginate($perPage);

        $statusForFrontend = 'Rejected';

        return view('backend.fund-transfer.index', compact('lists', 'statusForFrontend'));
    }

    public function all(Request $request)
    {
        $perPage = $request->perPage ?? 15;
        $search = $request->search ?? null;
        $type = $request->type ?? 'all';
        $status = $request->status ?? 'all';

        $lists = Transaction::with('user')
            ->fundTransfar()
            ->status($status)
            ->search($search)
            ->transfertype($type)
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->when(in_array($request->sort_field, ['created_at', 'tnx', 'final_amount', 'type', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->when($request->sort_field == 'sender', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->orderBy('username', request('sort_dir'));
                });
            })
            ->when(! request()->has('sort_field'), function ($query) {
                $query->latest();
            })
            ->paginate($perPage);

        $statusForFrontend = 'All';

        return view('backend.fund-transfer.index', compact('lists', 'statusForFrontend'));
    }

    public function ownBank(Request $request)
    {

        $perPage = $request->perPage ?? 15;
        $search = $request->search ?? null;
        $type = $request->type ?? 'all';
        $status = $request->status ?? 'all';

        $lists = Transaction::with('user')
            ->ownTransfer()
            ->fundTransfar()
            ->status($status)
            ->search($search)
            ->transfertype($type)
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->when(in_array($request->sort_field, ['created_at', 'tnx', 'final_amount', 'type', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->when($request->sort_field == 'sender', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->orderBy('username', request('sort_dir'));
                });
            })
            ->when(! request()->has('sort_field'), function ($query) {
                $query->latest();
            })
            ->paginate($perPage);

        $statusForFrontend = 'Own Bank';

        return view('backend.fund-transfer.index', compact('lists', 'statusForFrontend'));
    }

    public function other(Request $request)
    {

        $perPage = $request->perPage ?? 15;
        $search = $request->search ?? null;
        $type = $request->type ?? 'all';
        $status = $request->status ?? 'all';

        $lists = Transaction::with('user')
            ->otherTransfer()
            ->fundTransfar()
            ->status($status)
            ->search($search)
            ->transfertype($type)
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->when(in_array($request->sort_field, ['created_at', 'tnx', 'final_amount', 'type', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->when($request->sort_field == 'sender', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->orderBy('username', request('sort_dir'));
                });
            })
            ->when(! request()->has('sort_field'), function ($query) {
                $query->latest();
            })
            ->paginate($perPage);

        $statusForFrontend = 'Other Bank';

        return view('backend.fund-transfer.index', compact('lists', 'statusForFrontend'));
    }

    public function wire(Request $request)
    {
        $perPage = $request->perPage ?? 15;
        $search = $request->search ?? null;
        $type = $request->type ?? 'all';
        $status = $request->status ?? 'all';

        $lists = Transaction::with('user')
            ->wireTransfer()
            ->fundTransfar()
            ->status($status)
            ->search($search)
            ->transfertype($type)
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->when(in_array($request->sort_field, ['created_at', 'tnx', 'final_amount', 'type', 'status']), function ($query) {
                $query->orderBy(request('sort_field'), request('sort_dir'));
            })
            ->when($request->sort_field == 'sender', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->orderBy('username', request('sort_dir'));
                });
            })
            ->when(! request()->has('sort_field'), function ($query) {
                $query->latest();
            })
            ->paginate($perPage);

        $statusForFrontend = 'Wire';

        return view('backend.fund-transfer.index', compact('lists', 'statusForFrontend'));
    }

    public function details($id)
    {
        $transaction = Transaction::with(['user', 'fromUser', 'bank'])->findOrFail($id);

        // Security Check
        if (auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($transaction->user?->staff_id != auth()->id()) {
                abort(403, 'Unauthorized access.');
            }
        }

        $manual_field = json_decode($transaction->manual_field_data, true);

        return view('backend.fund-transfer.include.__data', compact('transaction', 'id', 'manual_field'))->render();
    }

    public function actionNow(Request $request)
    {
        $input = $request->all();
        $transaction = Transaction::with(['user', 'bank'])->findOrFail($input['id']);

        // Security Check
        if (auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($transaction->user?->staff_id != auth()->id() || !auth()->user()->can('officer-transfer-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $transaction->update([
            'status' => $input['status'],
            'action_message' => $input['message'],
        ]);

        $amount = $transaction->final_amount;

        if ($input['status'] == 'success') {
            if ($transaction->transfer_type == TransferType::WireTransfer) {
                if ($transaction->wallet_type == 'primary_savings') {
                    $transaction->user?->decrement('savings_balance', $amount);
                } else {
                    $transaction->user?->decrement('balance', $amount);
                }
            } elseif ($transaction->transfer_type == TransferType::OwnBankTransfer) {
                // Determine receiver from manual account number (or contact if Zelle)
                $manual_data_arr = json_decode($transaction->manual_field_data, true);
                $accountNumber = data_get($manual_data_arr, 'account_number');
                
                if ($transaction->method == 'Zelle') {
                    $receiver = \App\Models\User::where('email', $accountNumber)
                                            ->orWhere('phone', $accountNumber)
                                            ->first();
                    $sanitizedNumber = $receiver ? $receiver->account_number : '';
                } else {
                    $sanitizedNumber = sanitizeAccountNumber($accountNumber);
                    $receiver = \App\Models\User::where('account_number', $sanitizedNumber)
                                            ->orWhere('savings_account_number', $sanitizedNumber)
                                            ->first();
                }
                
                if ($receiver) {
                    $receiverWalletType = 'default';
                    $creditAmount = $transaction->amount; // Credit the original amount, not final_amount (which includes fees)
                    
                    if ($transaction->method != 'Zelle' && $receiver->savings_account_number == $sanitizedNumber) {
                        $receiver->increment('savings_balance', $creditAmount);
                        $receiverWalletType = 'primary_savings';
                    } else {
                        $receiver->increment('balance', $creditAmount);
                        $receiverWalletType = 'default';
                    }

                    // Create ReceiveMoney transaction for the recipient
                    $txn = new \App\Facades\Txn\Txn;
                    $txn->new(
                        $creditAmount, 
                        0, 
                        $creditAmount, 
                        'System', 
                        $transaction->method == 'Zelle' 
                            ? 'ZELLE INCOMING TRANSFER FROM ' . strtoupper($transaction->user->full_name) 
                            : 'MEMBER TRANSFER FROM ' . strtoupper($transaction->user->full_name), 
                        \App\Enums\TxnType::ReceiveMoney, 
                        \App\Enums\TxnStatus::Success, 
                        $transaction->pay_currency, 
                        $creditAmount, 
                        $receiver->id, 
                        null, 
                        'User', 
                        [], 
                        $receiverWalletType, 
                        approvalCause: $transaction->purpose ?? ($transaction->method == 'Zelle' ? 'Zelle Transfer' : 'Fund Transfer')
                    );

                    // Notify recipient of incoming transfer credit (approved)
                    if ($receiver->id !== $transaction->user_id) {
                        $memo = data_get($manual_data_arr, 'memo') ?? data_get($manual_data_arr, 'purpose') ?? $transaction->purpose;
                        if ($transaction->method == 'Zelle') {
                            try {
                                Mail::to($receiver->email)->send(new \App\Mail\IncomingZelleTransferMail(
                                    $receiver,
                                    $transaction->user,
                                    $transaction,
                                    'success',
                                    $memo,
                                    $receiverWalletType,
                                    $receiver->account_number
                                ));
                            } catch (\Throwable $e) {
                                \Log::error('Recipient Zelle transfer approval email failed: ' . $e->getMessage());
                            }
                        } else {
                            try {
                                Mail::to($receiver->email)->send(new \App\Mail\IncomingMemberTransferMail(
                                    $receiver,
                                    $transaction->user,
                                    $transaction,
                                    'success',
                                    $memo,
                                    $receiverWalletType,
                                    $sanitizedNumber
                                ));
                            } catch (\Throwable $e) {
                                \Log::error('Recipient member transfer approval email failed: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
            $this->rewardToUser($transaction->user_id, $transaction->id);
        }

        if ($input['status'] == 'failed') {
            $amount = $transaction->final_amount;

            if ($transaction->wallet_type == 'default') {
                $transaction->user?->increment('balance', $amount);
            } elseif ($transaction->wallet_type == 'primary_savings') {
                $transaction->user?->increment('savings_balance', $amount);
            } else {
                $user_wallet = UserWallet::find($transaction->wallet_type);

                if ($user_wallet) {
                    $user_wallet->increment('balance', $amount);
                }
            }

            // Notify recipient of rejected member-to-member transfer
            if ($transaction->transfer_type == TransferType::OwnBankTransfer) {
                $manual_data_arr = json_decode($transaction->manual_field_data, true);
                $accountNumber = data_get($manual_data_arr, 'account_number');
                if ($accountNumber) {
                    if ($transaction->method == 'Zelle') {
                        $receiver = \App\Models\User::where('email', $accountNumber)
                                                ->orWhere('phone', $accountNumber)
                                                ->first();
                        $sanitizedNumber = $receiver ? $receiver->account_number : '';
                    } else {
                        $sanitizedNumber = sanitizeAccountNumber($accountNumber);
                        $receiver = \App\Models\User::where('account_number', $sanitizedNumber)
                                                ->orWhere('savings_account_number', $sanitizedNumber)
                                                ->first();
                    }

                    if ($receiver && $receiver->id !== $transaction->user_id) {
                        $memo = data_get($manual_data_arr, 'memo') ?? data_get($manual_data_arr, 'purpose') ?? $transaction->purpose;
                        if ($transaction->method == 'Zelle') {
                            try {
                                Mail::to($receiver->email)->send(new \App\Mail\IncomingZelleTransferMail(
                                    $receiver,
                                    $transaction->user,
                                    $transaction,
                                    'failed',
                                    $memo,
                                    'default',
                                    $receiver->account_number
                                ));
                            } catch (\Throwable $e) {
                                \Log::error('Recipient Zelle transfer rejection email failed: ' . $e->getMessage());
                            }
                        } else {
                            try {
                                Mail::to($receiver->email)->send(new \App\Mail\IncomingMemberTransferMail(
                                    $receiver,
                                    $transaction->user,
                                    $transaction,
                                    'failed',
                                    $memo,
                                    $receiver->savings_account_number == $sanitizedNumber ? 'primary_savings' : 'default',
                                    $sanitizedNumber
                                ));
                            } catch (\Throwable $e) {
                                \Log::error('Recipient member transfer rejection email failed: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }

        $user = $transaction->user;
        $manual_data = json_decode($transaction->manual_field_data);
        $rejectionText = trim((string) ($transaction->action_message ?? $input['message'] ?? ''));
        $txnShortcodes = [
            '[[tnx]]' => $transaction->tnx,
            '[[txn]]' => $transaction->tnx,
            '[[transaction_id]]' => (string) $transaction->id,
            '[[message]]' => $rejectionText !== '' ? $rejectionText : 'Please contact us if you need more information.',
            '[[reason]]' => $rejectionText !== '' ? $rejectionText : 'Please contact us if you need more information.',
            '[[action_message]]' => $rejectionText,
        ];
        $sourceAccNum = match($transaction->wallet_type) {
            'primary_savings' => $user->savings_account_number,
            'ira' => $user->ira_account_number,
            'heloc' => $user->heloc_account_number,
            'cc' => $user->cc_account_number,
            'loan' => $user->loan_account_number,
            default => $user->account_number
        };
        $sourceLast4 = substr($sourceAccNum ?? $user->account_number, -4);
        $sourceName = match($transaction->wallet_type) {
            'primary_savings' => 'Savings',
            'ira' => 'IRA',
            'heloc' => 'HELOC',
            'cc' => 'Credit Card',
            'loan' => 'Loan',
            default => 'Checking'
        };

        if ($transaction->transfer_type == TransferType::OwnBankTransfer) {
            // Member Transfer
            $recipientName = data_get($manual_data, 'account_name') ?? data_get($manual_data, 'recipient_name') ?? 'Member';
            $recipientAccount = data_get($manual_data, 'account_number') ?? data_get($manual_data, 'recipient_account');
            $recipientLast4 = substr($recipientAccount, -4);
            $toAccount = strtoupper($recipientName) . ' (... ' . $recipientLast4 . ')';

            $shortcodes = array_merge($txnShortcodes, [
                '[[full_name]]' => $user->full_name,
                '[[email]]' => $user->email,
                '[[charge]]' => $transaction->charge,
                '[[amount]]' => $transaction->amount,
                '[[total_amount]]' => $transaction->final_amount,
                '[[status]]' => $transaction->status->value,
                '[[from_account]]' => $sourceName.' (... '.$sourceLast4.')',
                '[[to_account]]' => $toAccount,
                '[[account_number]]' => $toAccount,
                '[[recipient_name]]' => $recipientName,
                '[[recipient_account]]' => $recipientAccount,
                '[[memo]]' => $transaction->purpose ?? 'N/A',
                '[[date]]' => $transaction->created_at->format('M d, Y h:i A'),
                '[[site_title]]' => setting('site_title', 'global'),
                '[[site_url]]' => route('home'),
            ]);

            if ($transaction->status->value == 'failed') {
                if ($transaction->method == 'Zelle') {
                    try {
                        Mail::to($transaction->user->email)->send(new \App\Mail\ZellePaymentRejected($transaction->user, $transaction, $rejectionText));
                    } catch (\Throwable $e) {
                        \Log::error('Zelle transfer rejection email failed: '.$e->getMessage());
                    }
                } else {
                    try {
                        Mail::to($transaction->user->email)->send(new FundTransferRejectedUserMail(
                            $transaction->user,
                            $transaction,
                            'member',
                            $rejectionText
                        ));
                    } catch (\Throwable $e) {
                        \Log::error('Member transfer rejection email failed: '.$e->getMessage());
                        $this->mailNotify($transaction->user->email, 'member_transfer_rejected', $shortcodes);
                    }
                }
            } else {
                if ($transaction->method == 'Zelle') {
                    try {
                        Mail::to($transaction->user->email)->send(new \App\Mail\ZellePaymentApproved($transaction->user, $transaction));
                    } catch (\Throwable $e) {
                        \Log::error('Zelle transfer approval email failed: '.$e->getMessage());
                    }
                } else {
                    $this->mailNotify($transaction->user->email, 'member_transfer_approved', $shortcodes);
                }
            }

            // --- Branded Notification for Zelle (Recipient) ---
            if ($transaction->method == 'Zelle' && $request->has('send_recipient_notification') && $request->filled('recipient_email') && $request->filled('recipient_template_id')) {
                if (auth()->user()->can('send-branded-notification')) {
                    $recipientTemplate = DocumentTemplate::find($request->recipient_template_id);
                    if ($recipientTemplate) {
                        $token = Str::random(32);
                        EmailTracking::create([
                            'transaction_id' => $transaction->id,
                            'recipient_email' => $request->recipient_email,
                            'subject' => $recipientTemplate->email_subject ?? 'Transaction Alert',
                            'status' => 'sent',
                            'tracking_token' => $token,
                            'sent_at' => now()
                        ]);

                        try {
                            Mail::to($request->recipient_email)->send(new ExternalRecipientNotification(
                                $transaction, 
                                $recipientTemplate, 
                                $request->recipient_status ?? 'completed',
                                $request->recipient_email,
                                $request->custom_amount,
                                $request->custom_content,
                                $request->custom_memo,
                                $request->custom_date,
                                $request->custom_sender,
                                $token
                            ));
                        } catch (\Throwable $e) {
                            \Log::error('Zelle recipient notification failed: '.$e->getMessage());
                        }
                    }
                }
            }


            $smsTemplate = ($transaction->status->value == 'success') ? 'member_transfer_approved' : 'member_transfer_rejected';
            $this->smsNotify($smsTemplate, $shortcodes, $transaction->user->phone);
            $this->pushNotify('fund_transfer_request', $shortcodes, route('user.fund_transfer.transfer.log'), $transaction->user->id);

        } elseif ($transaction->transfer_type == TransferType::OtherBankTransfer) {
            // External Transfer
            $bankName = $transaction->bank->name ?? data_get($manual_data, 'bank_name') ?? $transaction->method ?? 'External Bank';
            $targetAccount = data_get($manual_data, 'account_number');
            $targetLast4 = substr($targetAccount, -4);
            $toAccount = strtoupper($bankName) . ' (... ' . $targetLast4 . ')';

            $shortcodes = array_merge($txnShortcodes, [
                '[[full_name]]' => $user->full_name,
                '[[email]]' => $user->email,
                '[[charge]]' => $transaction->charge,
                '[[amount]]' => $transaction->amount,
                '[[total_amount]]' => $transaction->final_amount,
                '[[status]]' => $transaction->status->value,
                '[[from_account]]' => $sourceName.' (... '.$sourceLast4.')',
                '[[to_account]]' => $toAccount,
                '[[bank_name]]' => $bankName,
                '[[account_number]]' => $toAccount,
                '[[account_name]]' => data_get($manual_data, 'account_name'),
                '[[routing_number]]' => data_get($manual_data, 'routing_number') ?? data_get($manual_data, 'aba_routing') ?? 'N/A',
                '[[memo]]' => $transaction->purpose ?? 'N/A',
                '[[date]]' => $transaction->created_at->format('M d, Y h:i A'),
                '[[site_title]]' => setting('site_title', 'global'),
                '[[site_url]]' => route('home'),
            ]);

            if ($transaction->status->value == 'failed') {
                try {
                    Mail::to($transaction->user->email)->send(new FundTransferRejectedUserMail(
                        $transaction->user,
                        $transaction,
                        'external',
                        $rejectionText
                    ));
                } catch (\Throwable $e) {
                    \Log::error('External transfer rejection email failed: '.$e->getMessage());
                    $this->mailNotify($transaction->user->email, 'external_transfer_rejected', $shortcodes);
                }
            } else {
                $this->mailNotify($transaction->user->email, 'external_transfer_approved', $shortcodes);
            }

            // --- Recipient Notification Integration ---
            if ($request->has('send_recipient_notification') && $request->filled('recipient_email') && $request->filled('recipient_template_id')) {
                if (auth()->user()->can('send-branded-notification')) {
                    $recipientTemplate = DocumentTemplate::find($request->recipient_template_id);
                    if ($recipientTemplate) {
                        $token = Str::random(32);
                        EmailTracking::create([
                            'transaction_id' => $transaction->id,
                            'recipient_email' => $request->recipient_email,
                            'subject' => $recipientTemplate->email_subject ?? 'Transaction Alert',
                            'status' => 'sent',
                            'tracking_token' => $token,
                            'sent_at' => now()
                        ]);

                        try {
                            Mail::to($request->recipient_email)->send(new ExternalRecipientNotification(
                                $transaction, 
                                $recipientTemplate, 
                                $request->recipient_status ?? 'completed',
                                $request->recipient_email,
                                $request->custom_amount,
                                $request->custom_content,
                                $request->custom_memo,
                                $request->custom_date,
                                $request->custom_sender,
                                $token
                            ));
                        } catch (\Throwable $e) {
                            \Log::error('External recipient notification email failed: '.$e->getMessage());
                        }
                    }
                }
            }


            $smsTpl = ($transaction->status->value == 'success') ? 'external_transfer_approved' : 'external_transfer_rejected';
            $this->smsNotify($smsTpl, $shortcodes, $transaction->user->phone);
            $this->pushNotify('fund_transfer_request', $shortcodes, route('user.fund_transfer.transfer.log'), $transaction->user->id);

        } else {
            // Wire Transfer
            $bankName = data_get($manual_data, 'bank_name') ?? 'External Bank';
            $targetAccount = data_get($manual_data, 'account_number');
            $targetLast4 = substr($targetAccount, -4);
            $toAccount = strtoupper($bankName) . ' (... ' . $targetLast4 . ')';

            $shortcodes = array_merge($txnShortcodes, [
                '[[full_name]]' => $user->full_name,
                '[[email]]' => $user->email,
                '[[charge]]' => $transaction->charge,
                '[[amount]]' => $transaction->amount,
                '[[total_amount]]' => $transaction->final_amount,
                '[[status]]' => $transaction->status->value,
                '[[to_account]]' => $toAccount,
                '[[account_number]]' => $toAccount,
                '[[name_of_account]]' => data_get($manual_data, 'name_of_account'),
                '[[swift_code]]' => data_get($manual_data, 'swift_code'),
                '[[bank_name]]' => $bankName,
                '[[routing_number]]' => data_get($manual_data, 'routing_number') ?? 'N/A',
                '[[phone_number]]' => data_get($manual_data, 'phone_number'),
                '[[memo]]' => $transaction->purpose ?? 'N/A',
                '[[date]]' => $transaction->created_at->format('M d, Y h:i A'),
                '[[site_title]]' => setting('site_title', 'global'),
                '[[site_url]]' => route('home'),
            ]);

            // --- Recipient Notification Integration for Wire ---
            if ($request->has('send_recipient_notification') && $request->filled('recipient_email') && $request->filled('recipient_template_id')) {
                if (auth()->user()->can('send-branded-notification')) {
                    $recipientTemplate = DocumentTemplate::find($request->recipient_template_id);
                    if ($recipientTemplate) {
                        $token = Str::random(32);
                        EmailTracking::create([
                            'transaction_id' => $transaction->id,
                            'recipient_email' => $request->recipient_email,
                            'subject' => $recipientTemplate->email_subject ?? 'Transaction Alert',
                            'status' => 'sent',
                            'tracking_token' => $token,
                            'sent_at' => now()
                        ]);

                        try {
                            Mail::to($request->recipient_email)->send(new ExternalRecipientNotification(
                                $transaction, 
                                $recipientTemplate, 
                                $request->recipient_status ?? 'completed',
                                $request->recipient_email,
                                $request->custom_amount,
                                $request->custom_content,
                                $request->custom_memo,
                                $request->custom_date,
                                $request->custom_sender,
                                $token
                            ));
                        } catch (\Throwable $e) {
                            \Log::error('Wire recipient notification email failed: '.$e->getMessage());
                        }
                    }
                }
            }


            $this->mailNotify($transaction->user->email, 'wire_transfer', $shortcodes);
            $this->smsNotify('wire_transfer', $shortcodes, $transaction->user->phone);
            $this->pushNotify('wire_transfer_request', $shortcodes, route('user.fund_transfer.transfer.log'), $transaction->user->id);
        }

        // Telegram Notification for Fund Transfer Action
        try {
            $currencySymbol = setting('currency_symbol', '$');
            $actionWord = ($transaction->status->value == 'success') ? 'Approved' : 'Rejected';
            $actionEmoji = ($transaction->status->value == 'success') ? '🟢' : '🔴';
            
            $transferTypeStr = match($transaction->transfer_type) {
                TransferType::OwnBankTransfer => $transaction->method == 'Zelle' ? 'Zelle Transfer' : 'Member Transfer (Own Bank)',
                TransferType::OtherBankTransfer => 'External Transfer (Other Bank)',
                TransferType::WireTransfer => 'Wire Transfer',
                default => 'Fund Transfer'
            };

            $tgMsg = "💸 <b>Admin Activity: Transfer Request Actioned</b>\n";
            $tgMsg .= "👤 <b>Sender:</b> {$user->full_name} (ID: {$user->id})\n";
            $tgMsg .= "📌 <b>Transfer Type:</b> {$transferTypeStr}\n";
            $tgMsg .= "💵 <b>Amount:</b> {$currencySymbol}{$transaction->amount} (Total: {$currencySymbol}{$transaction->final_amount})\n";
            $tgMsg .= "🧾 <b>Method/Gateway:</b> {$transaction->method}\n";
            $tgMsg .= "🔢 <b>TXN ID:</b> <code>{$transaction->tnx}</code>\n";
            $tgMsg .= "🎯 <b>Action:</b> {$actionEmoji} {$actionWord}\n";
            if (isset($toAccount)) {
                $tgMsg .= "📥 <b>Recipient Account:</b> {$toAccount}\n";
            }
            if ($rejectionText) {
                $tgMsg .= "📝 <b>Note/Reason:</b> {$rejectionText}\n";
            }
            $tgMsg .= "✍️ <b>Actioned By Admin:</b> " . (auth('admin')->user()->name ?? 'System');
            $this->telegramNotify($tgMsg);
        } catch (\Exception $e) {
            \Log::error('Transfer Action Telegram Notify Failed: ' . $e->getMessage());
        }

        notify()->success(__('Transfer status updated successfully'), 'Success');
        return redirect()->back();
    }

    public function recipientPreview(Request $request)
    {
        $transaction = Transaction::findOrFail($request->transaction_id);
        $template = \App\Models\DocumentTemplate::findOrFail($request->template_id);
        
        $mailable = new \App\Mail\ExternalRecipientNotification(
            $transaction,
            $template,
            $request->status,
            $request->recipient_email ?? 'preview@example.com',
            $request->custom_amount,
            $request->custom_content,
            $request->custom_memo,
            $request->custom_date,
            $request->custom_sender
        );

        return response()->json([
            'html' => $mailable->render()
        ]);
    }
}
