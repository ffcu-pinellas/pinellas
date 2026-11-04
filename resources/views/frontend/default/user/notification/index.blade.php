@extends('frontend::layouts.user')
@section('title')
    {{ __('Alerts & Notifications') }}
@endsection

@push('style')
<style>
    .nav-pills-custom .nav-link {
        color: var(--body-text-secondary-color, #64748b);
        background: #f1f5f9;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 10px 20px;
        border-radius: 50px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .nav-pills-custom .nav-link.active {
        color: #fff !important;
        background: #00549b !important;
        box-shadow: 0 4px 12px rgba(0, 84, 155, 0.25);
    }
    .dark-theme .nav-pills-custom .nav-link {
        background: #1e293b;
        color: #94a3b8;
    }
    .dark-theme .nav-pills-custom .nav-link.active {
        background: #00549b !important;
        color: #fff !important;
    }
    .alert-rule-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.2s ease;
    }
    .alert-rule-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }
    .dark-theme .alert-rule-card {
        background: #1e293b;
        border-color: #334155;
    }
    .banno-switch-lg {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
        cursor: pointer;
    }
    .banno-switch-lg input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .banno-switch-lg .slider {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 34px;
    }
    .banno-switch-lg .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .banno-switch-lg input:checked + .slider {
        background-color: #00549b;
    }
    .banno-switch-lg input:checked + .slider:before {
        transform: translateX(22px);
    }
    .threshold-input-group {
        max-width: 170px;
    }
    .threshold-input-group .input-group-text {
        background: #f8fafc;
        border-color: #cbd5e1;
        font-weight: 700;
        color: #475569;
    }
    .dark-theme .threshold-input-group .input-group-text {
        background: #0f172a;
        border-color: #475569;
        color: #cbd5e1;
    }
    .save-indicator {
        font-size: 0.8rem;
        transition: opacity 0.3s ease;
        opacity: 0;
    }
    .save-indicator.show {
        opacity: 1;
    }
</style>
@endpush

@section('content')
@php
    $user = auth()->user();
    $unreadCount = \App\Models\Notification::where('for', 'user')->where('user_id', $user->id)->where('read', 0)->count();
@endphp
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')
    
    <div class="col-xl-9 col-lg-8 col-md-12 col-12">
        <div class="site-card profile-details-view shadow-sm border-0" style="border-radius: 14px;">
            <div class="site-card-body p-4 p-md-5">
                
                <!-- Page Title -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h2 class="fw-bold mb-1" style="color: var(--body-text-primary-color); font-size: 1.6rem;">{{ __('Alerts & Notifications') }}</h2>
                        <p class="text-muted small mb-0">{{ __('Customize your smart alerts, transaction limits, and delivery channels.') }}</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span id="autoSaveBadge" class="save-indicator text-success fw-bold d-flex align-items-center gap-1">
                            <i class="fas fa-check-circle"></i> {{ __('Saved') }}
                        </span>
                    </div>
                </div>

                <!-- Dual Navigation Tabs -->
                <ul class="nav nav-pills nav-pills-custom gap-2 mb-4" id="alertTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active d-flex align-items-center gap-2" id="preferences-tab" data-bs-toggle="pill" data-bs-target="#preferences" type="button" role="tab" aria-selected="true">
                            <i class="fas fa-sliders-h"></i>
                            <span>{{ __('Smart Rules & Preferences') }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-2" id="activity-tab" data-bs-toggle="pill" data-bs-target="#activity" type="button" role="tab" aria-selected="false">
                            <i class="far fa-bell"></i>
                            <span>{{ __('Recent Activity') }}</span>
                            @if($unreadCount > 0)
                                <span class="badge bg-danger rounded-pill px-2" style="font-size: 0.7rem;">{{ $unreadCount }}</span>
                            @endif
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="alertTabContent">
                    
                    <!-- TAB 1: Smart Rules & Preferences -->
                    <div class="tab-pane fade show active" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                        
                        <form id="notificationPreferencesForm" action="{{ route('user.setting.update-notification-preferences') }}" method="POST">
                            @csrf

                            <!-- Section 1: Transaction & Balance Triggers -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-wallet small"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0" style="color: var(--body-text-primary-color); font-size: 1.1rem;">{{ __('Transaction & Balance Triggers') }}</h5>
                                </div>

                                <!-- Rule: Large Transaction Alert -->
                                <div class="alert-rule-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold fs-6 mb-1 text-dark">{{ __('Large Transaction Alert') }}</div>
                                            <p class="small text-muted mb-3">{{ __('Receive an instant notification whenever any single transaction or charge meets or exceeds your preset amount.') }}</p>
                                            
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="small text-muted fw-bold">{{ __('Notify for transactions over:') }}</span>
                                                <div class="input-group input-group-sm threshold-input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="1" min="1" max="1000000" name="large_txn_threshold" id="large_txn_threshold" class="form-control fw-bold" value="{{ $user->largeTxnThreshold() }}">
                                                </div>
                                            </div>
                                        </div>
                                        <label class="banno-switch-lg flex-shrink-0">
                                            <input type="checkbox" name="large_txn_alert" id="large_txn_alert" value="1" {{ $user->isLargeTxnAlertEnabled() ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Rule: Low Balance Warning -->
                                <div class="alert-rule-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold fs-6 mb-1 text-dark">{{ __('Low Balance Warning') }}</div>
                                            <p class="small text-muted mb-3">{{ __('Get an early warning before your checking or savings balance drops below a safe threshold to help avoid overdrafts.') }}</p>
                                            
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="small text-muted fw-bold">{{ __('Notify when balance falls below:') }}</span>
                                                <div class="input-group input-group-sm threshold-input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="1" min="0" max="1000000" name="low_balance_threshold" id="low_balance_threshold" class="form-control fw-bold" value="{{ $user->lowBalanceThreshold() }}">
                                                </div>
                                            </div>
                                        </div>
                                        <label class="banno-switch-lg flex-shrink-0">
                                            <input type="checkbox" name="low_balance_alert" id="low_balance_alert" value="1" {{ $user->isLowBalanceAlertEnabled() ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Rule: Direct Deposit & Payroll -->
                                <div class="alert-rule-card">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <div class="fw-bold fs-6 mb-1 text-dark">{{ __('Direct Deposit & Payroll Credits') }}</div>
                                            <p class="small text-muted mb-0">{{ __('Notify me immediately when payroll, wire deposits, or direct deposits settle in my account.') }}</p>
                                        </div>
                                        <label class="banno-switch-lg flex-shrink-0">
                                            <input type="checkbox" name="direct_deposit_alert" id="direct_deposit_alert" value="1" {{ $user->isDirectDepositAlertEnabled() ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Rule: Card Charges -->
                                <div class="alert-rule-card">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <div class="fw-bold fs-6 mb-1 text-dark">{{ __('Debit & Credit Card Charges') }}</div>
                                            <p class="small text-muted mb-0">{{ __('Real-time push and email alerts on all in-store POS swipes, ATM withdrawals, and online purchases.') }}</p>
                                        </div>
                                        <label class="banno-switch-lg flex-shrink-0">
                                            <input type="checkbox" name="card_charge_alert" id="card_charge_alert" value="1" {{ $user->isCardChargeAlertEnabled() ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Rule: Wire & Zelle -->
                                <div class="alert-rule-card">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <div class="fw-bold fs-6 mb-1 text-dark">{{ __('Zelle® & Wire Transfer Activity') }}</div>
                                            <p class="small text-muted mb-0">{{ __('Receive instant alerts for incoming Zelle® payments, completed member transfers, and wire dispatches.') }}</p>
                                        </div>
                                        <label class="banno-switch-lg flex-shrink-0">
                                            <input type="checkbox" name="zelle_transfer_alert" id="zelle_transfer_alert" value="1" {{ $user->getNotificationPreference('zelle_transfer_alert', true) ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Security & Account Integrity -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-shield-alt small"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0" style="color: var(--body-text-primary-color); font-size: 1.1rem;">{{ __('Security & Sign-in Alerts') }}</h5>
                                </div>

                                <!-- Rule: New Device Login -->
                                <div class="alert-rule-card">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <div class="fw-bold fs-6 mb-1 text-dark">{{ __('New Device & Unrecognized Sign-In') }}</div>
                                            <p class="small text-muted mb-0">{{ __('Receive an instant alert whenever your account is accessed from a new browser, IP location, or mobile device.') }}</p>
                                        </div>
                                        <label class="banno-switch-lg flex-shrink-0">
                                            <input type="checkbox" name="new_device_login_alert" id="new_device_login_alert" value="1" {{ $user->isNewDeviceAlertEnabled() ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Rule: Security Changes (Mandatory) -->
                                <div class="alert-rule-card bg-light border-0">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <div class="fw-bold fs-6 mb-1 text-dark">{{ __('Security & Profile Credential Updates') }}</div>
                                            <p class="small text-muted mb-0">{{ __('Critical alerts regarding password changes, PIN updates, and 2FA modifications are mandatory for your protection.') }}</p>
                                        </div>
                                        <span class="badge bg-secondary text-white small px-3 py-2 rounded-pill flex-shrink-0">
                                            <i class="fas fa-lock me-1"></i>{{ __('Always Active') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Delivery Channels -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-paper-plane small"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0" style="color: var(--body-text-primary-color); font-size: 1.1rem;">{{ __('Delivery Channels') }}</h5>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="alert-rule-card h-100 mb-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold text-dark mb-1"><i class="fas fa-bell me-2 text-primary"></i>Push & In-App Alerts</div>
                                                    <div class="small text-muted">Receive live popups on web & mobile</div>
                                                </div>
                                                <label class="banno-switch-lg flex-shrink-0">
                                                    <input type="checkbox" name="push_alerts_enabled" id="push_alerts_enabled" value="1" {{ $user->isPushAlertEnabled() ? 'checked' : '' }}>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert-rule-card h-100 mb-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold text-dark mb-1"><i class="fas fa-envelope me-2 text-primary"></i>Email Notifications</div>
                                                    <div class="small text-muted">Sent to: {{ $user->email }}</div>
                                                </div>
                                                <label class="banno-switch-lg flex-shrink-0">
                                                    <input type="checkbox" name="email_alerts_enabled" id="email_alerts_enabled" value="1" {{ $user->isEmailAlertsEnabled() ? 'checked' : '' }}>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Explicit Save Button for Desktop/Mobile -->
                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" id="savePreferencesBtn">
                                    <i class="fas fa-check me-2"></i>{{ __('Save Preferences') }}
                                </button>
                            </div>

                        </form>
                    </div>

                    <!-- TAB 2: Recent Activity & Notifications List -->
                    <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-uppercase small text-muted mb-0" style="letter-spacing: 0.5px;">{{ __('Recent Notifications') }}</h6>
                            @if($unreadCount > 0)
                                <a href="{{ route('user.read-notification', 0) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                    <i class="fas fa-check-double me-1"></i>{{ __('Mark all as read') }}
                                </a>
                            @endif
                        </div>

                        <div class="notification-list banno-list">
                            @forelse($notifications as $notification)
                                <div class="banno-toggle {{ $notification->read ? 'read' : 'unread' }}" style="padding: 1.25rem; border: 1px solid #f0f2f5; border-radius: 12px; margin-bottom: 1rem; background: {{ $notification->read ? '#fff' : '#f8faff' }};">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="icon-circle me-3" style="width: 40px; height: 40px; border-radius: 50%; background: #eef2f7; display: flex; align-items: center; justify-content: center; color: #00549b;">
                                            <i data-lucide="{{ $notification->icon ?: 'bell' }}" style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <div class="contents flex-grow-1">
                                            <div class="fw-bold" style="color: var(--body-text-primary-color); font-size: 0.95rem;">{{ $notification->title }}</div>
                                            <div class="time text-muted small"> {{ $notification->created_at->diffForHumans() }}</div>
                                        </div>
                                        <div class="link ms-3">
                                            <a href="{{ route('user.read-notification', $notification->id) }}" class="text-primary fw-bold small text-decoration-none">
                                                {{ __('View Details') }} &rarr;
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            <div class="text-center py-5">
                                <div class="rounded-circle bg-light d-inline-flex p-4 mb-3 text-muted">
                                    <i data-lucide="bell-off" style="width: 36px; height: 36px;"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ __('No notifications yet') }}</h6>
                                <p class="mb-0 text-muted small">{{ __('When you make transfers, receive deposits, or configure alerts, they will show up here.') }}</p>
                            </div>
                            @endforelse
                        </div>
                        
                        <div class="mt-4">
                            {{ $notifications->links() }}
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Hash tab activator
        if(window.location.hash === '#activity') {
            $('#activity-tab').tab('show');
        } else if(window.location.hash === '#preferences') {
            $('#preferences-tab').tab('show');
        }

        // Auto-save debounced handler for smart rules
        let saveTimer;
        function autoSavePreferences() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(function() {
                const formData = $('#notificationPreferencesForm').serialize();
                const submitBtn = $('#savePreferencesBtn');
                const badge = $('#autoSaveBadge');
                
                $.ajax({
                    url: '{{ route("user.setting.update-notification-preferences") }}',
                    method: 'POST',
                    data: formData,
                    beforeSend: function() {
                        badge.removeClass('show text-success').addClass('show text-muted').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                    },
                    success: function(response) {
                        badge.removeClass('text-muted').addClass('text-success').html('<i class="fas fa-check-circle"></i> Saved').fadeIn();
                        setTimeout(() => badge.removeClass('show'), 2500);
                    },
                    error: function(xhr) {
                        badge.removeClass('text-muted').addClass('text-danger').html('<i class="fas fa-exclamation-triangle"></i> Error saving').fadeIn();
                    }
                });
            }, 600);
        }

        // Trigger auto-save on switch toggles and input changes
        $('#notificationPreferencesForm input[type="checkbox"]').on('change', autoSavePreferences);
        $('#notificationPreferencesForm input[type="number"]').on('input change', autoSavePreferences);

        // Form submit directly
        $('#notificationPreferencesForm').on('submit', function(e) {
            e.preventDefault();
            autoSavePreferences();
        });
    });
</script>
@endpush
