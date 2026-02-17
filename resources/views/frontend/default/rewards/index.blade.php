@extends('frontend::layouts.user')
@section('title')
    {{ __('Rewards') }}
@endsection
@section('content')
    <div class="row">
        {{-- Navigation Sidebar --}}
        <div class="col-xl-3 col-lg-4 col-md-12 col-12">
            <div class="site-card profile-nav mb-4">
                <div class="site-card-body p-0">
                    <div class="settings-nav-group">
                        <div class="nav-group-title">MEMBER REWARDS</div>
                        <ul class="settings-nav-list">
                            <li class="active">
                                <a href="#overview" data-bs-toggle="tab">
                                    <i data-lucide="award"></i>
                                    <span>{{ __('Overview') }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="#earnings" data-bs-toggle="tab">
                                    <i data-lucide="trending-up"></i>
                                    <span>{{ __('How to Earn') }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="#history" data-bs-toggle="tab">
                                    <i data-lucide="history"></i>
                                    <span>{{ __('Redeem History') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Points Summary Card --}}
            <div class="site-card bg-primary text-white mb-4">
                <div class="site-card-body text-center py-4">
                    <div class="small opacity-75 mb-1 text-uppercase fw-bold">{{ __('Available Points') }}</div>
                    <h2 class="display-5 fw-bold mb-3">{{ auth()->user()->points }}</h2>
                    @if($myPortfolio)
                        <div class="small opacity-75 mb-4">
                            {{ __(":points points = :amount", ['points' => $myPortfolio->point, 'amount' => $currencySymbol.$myPortfolio->amount]) }}
                        </div>
                        <a href="{{ route('user.rewards.redeem.now') }}" class="btn btn-light w-100 fw-bold redeem-btn">
                            <i data-lucide="gift" class="me-2"></i>{{ __('Redeem Now') }}
                        </a>
                    @else
                        <div class="small opacity-75">{{ __('Points redemption currently unavailable.') }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-xl-9 col-lg-8 col-md-12 col-12">
            <div class="tab-content">
                {{-- Overview Tab --}}
                <div class="tab-pane fade show active" id="overview">
                    <div class="site-card profile-details-view mb-4">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Rewards Overview') }}</h3>
                        </div>
                        <div class="site-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="reward-banner p-4 rounded-3 text-white h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, #FF9966 0%, #FF5E62 100%);">
                                        <div>
                                            <h4 class="fw-bold mb-2">{{ __('Earn on Every Transaction') }}</h4>
                                            <p class="small mb-0 opacity-75">{{ __('Get points for using your checking and savings accounts.') }}</p>
                                        </div>
                                        <div class="mt-4">
                                            <i data-lucide="zap" size="48" class="opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="reward-banner p-4 rounded-3 text-white h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, #00B4DB 0%, #0083B0 100%);">
                                        <div>
                                            <h4 class="fw-bold mb-2">{{ __('Level Up Your Benefits') }}</h4>
                                            <p class="small mb-0 opacity-75">{{ __('Your tier is determined by your account portfolio.') }}</p>
                                        </div>
                                        <div class="mt-4">
                                            <i data-lucide="layers" size="48" class="opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5">
                                <h5 class="fw-bold mb-3">{{ __('Redemption Tiers') }}</h5>
                                <div class="table-responsive">
                                    <table class="table site-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Portfolio Tier') }}</th>
                                                <th>{{ __('Points Required') }}</th>
                                                <th>{{ __('Value') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($redeems as $redeem)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-xs bg-light me-2 rounded-circle d-flex align-items-center justify-content-center">
                                                                <i data-lucide="shield-check" class="text-primary" size="14"></i>
                                                            </div>
                                                            <span class="fw-bold text-dark">{{ $redeem->portfolio->portfolio_name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $redeem->point }} {{ __('Points') }}</td>
                                                    <td class="fw-bold text-success">{{ $currencySymbol . $redeem->amount }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Earnings Tab --}}
                <div class="tab-pane fade" id="earnings">
                    <div class="site-card profile-details-view mb-4">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('How to Earn Points') }}</h3>
                        </div>
                        <div class="site-card-body">
                            <div class="table-responsive">
                                <table class="table site-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Activity') }}</th>
                                            <th>{{ __('Required Amount') }}</th>
                                            <th>{{ __('Points Earned') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($earnings as $earning)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $earning->portfolio->portfolio_name }} {{ __('Activity') }}</div>
                                                    <div class="small text-muted">{{ __('Qualifying transaction') }}</div>
                                                </td>
                                                <td>{{ $currencySymbol . $earning->amount_of_transactions }}</td>
                                                <td>
                                                    <span class="site-badge badge-success">+{{ $earning->point }} {{ __('Points') }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- History Tab --}}
                <div class="tab-pane fade" id="history">
                    <div class="site-card profile-details-view">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Redeem History') }}</h3>
                        </div>
                        <div class="site-card-body p-0">
                            <div class="table-responsive">
                                <table class="table site-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">{{ __('Description') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th class="pe-4">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transactions as $transaction)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark">{{ $transaction->description }}</div>
                                                    <div class="small text-muted">ID: {{ $transaction->trx }}</div>
                                                </td>
                                                <td class="fw-bold text-success">
                                                    +{{ $currencySymbol . number_format($transaction->amount, 2) }}
                                                </td>
                                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                                <td class="pe-4">
                                                    <span class="site-badge badge-success">{{ __('Success') }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <div class="text-muted">{{ __('No redemption history found.') }}</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($transactions->hasPages())
                                <div class="p-3 border-top">
                                    {{ $transactions->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            "use strict";
            $('.redeem-btn').on('click', function() {
                $(this).addClass('disabled').html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Processing...') }}');
            });
            
            // Handle tab persistence if needed
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                window.location.hash = e.target.hash;
            });
            
            let hash = window.location.hash;
            if (hash) {
                $('.settings-nav-list a[href="' + hash + '"]').tab('show');
                $('.settings-nav-list li').removeClass('active');
                $('.settings-nav-list a[href="' + hash + '"]').parent().addClass('active');
            }
            
            $('.settings-nav-list a').on('click', function() {
                $('.settings-nav-list li').removeClass('active');
                $(this).parent().addClass('active');
            });
        });
    </script>
@endsection

@push('style')
<style>
    .site-table thead th {
        background: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-bottom: 2px solid #edf2f9;
        padding: 1rem;
    }
    .site-table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #edf2f9;
    }
    .reward-banner {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: none;
    }
    .site-badge {
        font-weight: 600;
        padding: 0.35em 0.8em;
        border-radius: 50rem;
    }
    .badge-success {
        background-color: #d1fae5;
        color: #065f46;
    }
</style>
@endpush
