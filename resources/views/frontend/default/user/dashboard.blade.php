@extends('frontend::layouts.user')
@section('title')
{{ __('Dashboard') }}
@endsection
@section('content')

<div class="row">
    <div class="col-xl-4 col-lg-12 col-md-12 col-12">
        <div class="user-profile-card">
            @if (setting('user_portfolio', 'permission') && Auth::user()->portfolio_status && auth()->user()->portfolio_id != null)
            <div class="badge">
                <a href="{{ route('user.portfolio') }}" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="top" data-bs-title="{{ auth()->user()->portfolio?->portfolio_name }}">
                    <img src="{{ asset(auth()->user()->portfolio?->icon) }}" alt="">
                </a>
            </div>
            @endif

            <input type="hidden" id="refLink" value="{{ auth()->user()->account_number }}">

            <h4 class="title">{{ __('Default Account') }}</h4>

            <h3 class="acc-balance" id="passo">
                {{ setting('currency_symbol','global').number_format($user->balance,2) }}
            </h3>

            <div class="acc-num">A/C:
                <strong>{{ auth()->user()->account_number }}</strong>
                <span id="copy" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="Copy"><i data-lucide="copy"></i></span>
            </div>

            @php
            $last_login = auth()->user()->activities->last();
            $browser = getBrowser($last_login?->agent);
            @endphp
            @if($last_login)
            <div class="last-login">{{ __('Last Login At') }} {{ $last_login?->created_at->format('d M, h:i A') }}. {{ data_get($browser,'platform') }} . {{ data_get($browser,'browser') }}</div>
            @endif
            <div class="buttons">
                @if (setting('multiple_currency', 'permission'))
                <a href="{{ route('user.all-wallets') }}" class="send me-2"><i data-lucide="credit-card"></i>{{ __('All Wallets') }}</a>
                @endif
                
            </div>
            <div class="o">O</div>
            
            <!-- Enhanced Quick Actions Section -->
            <div class="quick-actions-container" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 20px; margin-top: 20px;">
                <div class="quick-actions-scroll-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none;">
                    <div class="quick-actions-scroll-wrapper::-webkit-scrollbar" style="display: none;"></div>
                    <div class="quick-actions-grid" style="display: flex; gap: 12px; min-width: max-content; padding-bottom: 4px;">
                        
                        <!-- Transfer -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.fund_transfer.index') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="arrow-left-right" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">Transfer</span>
                            </a>
                        </div>

                        <!-- Deposit -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.deposit.amount') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">Deposit</span>
                            </a>
                        </div>

                        <!-- Pay -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.pay.bill.electricity') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="dollar-sign" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">Pay</span>
                            </a>
                        </div>

                        @if (setting('multiple_currency', 'permission'))
                        <!-- All Wallets -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.all-wallets') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">Wallets</span>
                            </a>
                        </div>
                        @endif

                        @if (Route::has('user.card.index'))
                        <!-- Virtual Card -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.card.index') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">V. Card</span>
                            </a>
                        </div>
                        @endif

                        @if (Route::has('user.rewards.index'))
                        <!-- Rewards -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.rewards.index') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="gift" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">Rewards</span>
                            </a>
                        </div>
                        @endif

                        @if(setting('user_dps','permission'))
                        <!-- DPS -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.dps.history') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="archive" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">DPS</span>
                            </a>
                        </div>
                        @endif

                        @if(setting('user_fdr','permission'))
                        <!-- FDR -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.fdr.history') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="book" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">FDR</span>
                            </a>
                        </div>
                        @endif

                        @if(setting('user_loan','permission'))
                        <!-- Loan -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.loan.history') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="alert-triangle" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">Loan</span>
                            </a>
                        </div>
                        @endif
                        
                         <!-- Message -->
                        <div class="quick-action-item" style="flex: 0 0 auto; width: 70px;">
                            <a href="{{ route('user.ticket.index') }}" class="quick-action-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: white; padding: 12px 8px; border-radius: 12px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);" 
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i data-lucide="message-square" style="width: 18px; height: 18px;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; text-align: center; line-height: 1.2;">Message</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-8 col-lg-12 col-md-12 col-12">


<!-- Transactions Card (moved above feature cards) -->
<div class="site-card mb-4 mt-4" style="background: var(--transaction-card-bg); border: 1px solid var(--transaction-card-border);">
    <div class="site-card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold h5 mb-0 text-primary">{{ __('Transactions') }}</span>
        <a href="{{ route('user.transactions') }}" class="btn btn-link btn-sm" style="color: var(--transaction-card-link);">{{ __('See more') }}</a>
    </div>
    <div class="site-card-body p-0">
        @forelse ($recentTransactions as $transaction)
        <div class="d-flex align-items-center justify-content-between py-3 px-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                    @if($transaction->type->value == 'deposit' || $transaction->type->value == 'manual_deposit')
                    <i data-lucide="chevrons-down" class="text-success"></i>
                    @elseif(Str::startsWith($transaction->type->value ,'dps'))
                    <i data-lucide="archive" class="text-info"></i>
                    @elseif(Str::startsWith($transaction->type->value ,'fdr'))
                    <i data-lucide="book" class="text-primary"></i>
                    @elseif(Str::startsWith($transaction->type->value ,'loan'))
                    <i data-lucide="alert-triangle" class="text-warning"></i>
                    @elseif($transaction->type->value == 'subtract')
                    <i data-lucide="minus-circle" class="text-danger"></i>
                    @elseif($transaction->type->value == 'receive_money')
                    <i data-lucide="arrow-down-left" class="text-success"></i>
                    @else
                    <i data-lucide="send" class="text-secondary"></i>
                    @endif
                </span>
                <div>
                    <div class="fw-bold small" style="color: var(--transaction-card-text);">{{ $transaction->description }}</div>
                    <div class="text-muted small">{{ $transaction->created_at }}</div>
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold {{ isPlusTransaction($transaction->type) ? 'text-success' : 'text-danger' }}">
                    {{ isPlusTransaction($transaction->type) ? '+' : '-' }}{{ $transaction->amount.' '.transaction_currency($transaction) }}
                </div>
                <div class="small text-muted">{{ ucfirst(str_replace('_',' ',$transaction->type->value)) }}</div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-4">{{ __('No Data Found') }}</div>
        @endforelse
    </div>
</div>
        <!-- Feature Cards: My DPS, FDR, Loan (darker background) -->
        <div class="row g-3">
            @if(setting('user_dps','permission'))
            <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                <div class="modern-feature-card shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between" style="background: var(--bs-card-bg, #ececf1);">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle me-3 bg-primary" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                            <i data-lucide="archive" class="text-light" style="font-size:1.5rem;"></i>
                        </div>
                        <div>
                            <div class="fw-bold mb-1 text-primary" style="font-size:1.1rem;">My Individual Retirement Accounts (IRAs)</div>
                            <div class="small text-muted">@if($total_running_dps > 0)
                                @foreach($user->dps->whereIn('status',[App\Enums\DpsStatus::Running,App\Enums\DpsStatus::Due]) ?? [] as $dps)
                                    {{ $dps->plan?->name }} - <strong>{{ $dps->last_date }}</strong>{!! !$loop->last ? ', <br>' : '' !!}
                                @endforeach
                            @else
                                Currently No IRAs Found.
                            @endif</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-auto">
                        <div class="amount fw-bold text-primary" style="font-size:1.3rem;">
                            @if($total_running_dps > 0)
                                {{ $currencySymbol.number_format($dps_mature_amount,2) }}
                            @else
                                {{ $currencySymbol.number_format(0,2) }}
                            @endif
                        </div>
                        <a href="{{ route('user.dps.history') }}" class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center gap-1">View <i data-lucide="arrow-right"></i></a>
                    </div>
                </div>
            </div>
            @endif
            @if(setting('user_fdr','permission'))
            <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                <div class="modern-feature-card shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between" style="background: var(--bs-card-bg, #ececf1);">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle me-3 bg-info" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                            <i data-lucide="book" class="text-light" style="font-size:1.5rem;"></i>
                        </div>
                        <div>
                            <div class="fw-bold mb-1 text-info" style="font-size:1.1rem;">My Certificate of Deposit (CD)</div>
                            <div class="small text-muted">@if($total_running_fdr > 0)
                                @foreach($user->fdr->where('status',App\Enums\FdrStatus::Running) ?? [] as $fdr)
                                    {{ $fdr->plan?->name }} - <strong>{{ $fdr->last_date->format('d M Y') }}</strong>{!! !$loop->last ? ', <br>' : '' !!}
                                @endforeach
                            @else
                                Currently No CD Found.
                            @endif</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-auto">
                        <div class="amount fw-bold text-info" style="font-size:1.3rem;">
                            @if($total_running_fdr > 0)
                                {{ $currencySymbol.number_format($fdr_mature_amount,2) }}
                            @else
                                {{ $currencySymbol.number_format(0,2) }}
                            @endif
                        </div>
                        <a href="{{ route('user.fdr.history') }}" class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center gap-1">View <i data-lucide="arrow-right"></i></a>
                    </div>
                </div>
            </div>
            @endif
            @if(setting('user_loan','permission'))
            <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                <div class="modern-feature-card shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between" style="background: var(--bs-card-bg, #ececf1);">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle me-3 bg-warning" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                            <i data-lucide="alert-triangle" class="text-light" style="font-size:1.5rem;"></i>
                        </div>
                        <div>
                            <div class="fw-bold mb-1 text-warning" style="font-size:1.1rem;">My Loans</div>
                            <div class="small text-muted">@if($total_running_loan > 0)
                                @foreach($user->loan->whereIn('status', [\App\Enums\LoanStatus::Running, \App\Enums\LoanStatus::Due]) ?? [] as $loan)
                                    {{ $loan->plan?->name }} - @if($loan->last_date)<strong>{{ $loan->last_date->format('d M Y') }}</strong>@else<strong>N/A</strong>@endif{!! !$loop->last ? ', <br>' : '' !!}
                                @endforeach
                            @else
                                Currently No Loan Found.
                            @endif</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-auto">
                        <div class="amount fw-bold text-warning" style="font-size:1.3rem;">
                            @if($total_running_loan > 0)
                                {{ $currencySymbol.number_format($total_loan_amount,2) }}
                            @else
                                {{ $currencySymbol.number_format(0,2) }}
                            @endif
                        </div>
                        <a href="{{ route('user.loan.history') }}" class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center gap-1">View <i data-lucide="arrow-right"></i></a>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <!-- Messages Card -->
        <div class="site-card mt-4" style="background: var(--bs-card-bg, #ececf1);">
            <div class="site-card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold h5 mb-0 text-primary">{{ __('Messages') }}</span>
            </div>
            <div class="site-card-body text-center py-5">
                <i data-lucide="mail" class="mb-2 text-primary" style="font-size:2rem;"></i>
                <div class="fw-semibold text-muted" style="font-size:1.1rem;">You have no unread messages.</div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    $('#copy').on('click', function() {
        copyRef();
    });

    function copyRef() {
        /* Get the text field */
        var textToCopy = $('#refLink').val();
        // Create a temporary input element
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(textToCopy).select();
        // Copy the text from the temporary input
        document.execCommand('copy');
        // Remove the temporary input element
        tempInput.remove();

        // Set tooltip as copied
        var tooltip = bootstrap.Tooltip.getInstance('#copy');
        tooltip.setContent({
            '.tooltip-inner': 'Copied'
        });

        setTimeout(() => {
            tooltip.setContent({
                '.tooltip-inner': 'Copy'
            });
        }, 4000);
    }
</script>

<style>
/* Additional CSS for better mobile experience */
@media (max-width: 768px) {
    .quick-actions-container {
        margin: 15px -15px 0 -15px;
        border-radius: 5 !important;
    }
    
    .quick-actions-scroll-wrapper {
        padding: 0 15px;
    }
}

/* Custom scrollbar for webkit browsers */
.quick-actions-scroll-wrapper::-webkit-scrollbar {
    height: 2px;
}

.quick-actions-scroll-wrapper::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 1px;
}

.quick-actions-scroll-wrapper::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 1px;
}

.quick-actions-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}

/* Light mode colors */
:root {
    --transaction-card-bg: #f0f9f0;
    --transaction-card-border: #d4edda;
    --transaction-card-text: #155724;
    --transaction-card-link: #0d4f1c;
}

/* Dark mode colors */
@media (prefers-color-scheme: dark) {
    :root {
        --transaction-card-bg: #1a2e1a;
        --transaction-card-border: #2d4a2d;
        --transaction-card-text: #90c695;
        --transaction-card-link: #a8d4ad;
    }
}

/* Manual dark mode class support */
[data-bs-theme="dark"], .dark-mode {
    --transaction-card-bg: #1a2e1a;
    --transaction-card-border: #2d4a2d;
    --transaction-card-text: #90c695;
    --transaction-card-link: #a8d4ad;
}
</style>

@endsection