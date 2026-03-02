@extends('frontend::layouts.user')
@section('title')
    {{ __('Email & Notifications') }}
@endsection
@section('content')
    <div class="row">
        @include('frontend::user.setting.include.__settings_nav')
        <div class="col-xl-9 col-lg-8 col-md-12 col-12">
            <div class="site-card profile-details-view">
                <div class="site-card-body">
                    <div class="section-title mb-4">
                        <h3 style="font-weight: 700; color: var(--body-text-primary-color); margin: 0;">{{ __('Email & Notifications') }}</h3>
                        <p style="color: var(--body-text-secondary-color); font-size: 0.9rem;">{{ __('Review your recent alerts and manage how you receive updates.') }}</p>
                    </div>

                    <div class="notification-list banno-list mt-4">
                        @forelse($notifications as $notification)
                            <div class="banno-toggle {{ $notification->read ? 'read' : 'unread' }}" style="padding: 1.25rem; border: 1px solid #f0f2f5; border-radius: 12px; margin-bottom: 1rem; background: {{ $notification->read ? '#fff' : '#f8faff' }};">
                                <div class="d-flex align-items-center w-100">
                                    <div class="icon-circle me-3" style="width: 40px; height: 40px; border-radius: 50%; background: #eef2f7; display: flex; align-items: center; justify-content: center; color: var(--body-text-theme-color);">
                                        <i data-lucide="{{ $notification->icon ?: 'bell' }}" style="width: 20px; height: 20px;"></i>
                                    </div>
                                    <div class="contents flex-grow-1">
                                        <div class="fw-bold" style="color: var(--body-text-primary-color); font-size: 0.95rem;">{{ $notification->title }}</div>
                                        <div class="time" style="color: var(--body-text-secondary-color); font-size: 0.8rem;"> {{ $notification->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="link ms-3">
                                        <a href="{{ route('user.read-notification', $notification->id) }}"
                                           class="text-primary fw-bold" style="font-size: 0.85rem;">{{ __('View Details') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                        <div class="text-center py-5">
                            <i data-lucide="bell-off" class="mb-3 text-muted" style="width: 48px; height: 48px;"></i>
                            <p class="mb-0 text-muted">{{ __('No notifications found.') }}</p>
                        </div>
                        @endforelse
                    </div>
                    
                    <div class="mt-4">
                         {{ $notifications->links() }}
                    </div>

                    <hr class="mt-5 mb-4">

                    <div class="settings-footer-actions">
                         <h5 class="mb-3" style="font-weight: 700; color: var(--body-text-primary-color);">{{ __('Notification Settings') }}</h5>
                         <div class="banno-toggle">
                             <div class="banno-toggle-info">
                                 <h6>{{ __('Push Notifications') }}</h6>
                                 <p>{{ __('Receive real-time alerts on your mobile device.') }}</p>
                             </div>
                             <div class="banno-switch active"></div>
                         </div>
                         <div class="banno-toggle">
                             <div class="banno-toggle-info">
                                 <h6>{{ __('Email Alerts') }}</h6>
                                 <p>{{ __('Receive weekly account summaries and security alerts via email.') }}</p>
                             </div>
                             <div class="banno-switch active"></div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
