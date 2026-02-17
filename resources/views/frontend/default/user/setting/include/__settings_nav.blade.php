<div class="col-xl-3 col-lg-4 col-md-12 col-12">
    <div class="site-card settings-sidebar">
        <div class="site-card-body">
            <div class="settings-group">
                <h6 class="settings-group-title">{{ __('PERSONAL') }}</h6>
                <ul class="settings-nav-list">
                    <li><a href="{{ route('user.setting.show') }}" class="{{ isActive('user.setting.show') }}"><i data-lucide="user"></i> {{ __('Profile') }}</a></li>
                    <li><a href="{{ route('user.setting.security') }}" class="{{ isActive('user.setting.security') }}"><i data-lucide="lock"></i> {{ __('Security') }}</a></li>
                    <li><a href="{{ route('user.kyc') }}" class="{{ isActive('user.kyc*') }}"><i data-lucide="file-check"></i> {{ __('ID Verification') }}</a></li>
                </ul>
            </div>
            <div class="settings-group mt-4">
                <h6 class="settings-group-title">{{ __('ACCOUNTS') }}</h6>
                <ul class="settings-nav-list">
                    <li><a href="{{ route('user.accounts') }}" class="{{ isActive('user.accounts') }}"><i data-lucide="credit-card"></i> {{ __('Account Settings') }}</a></li>
                    <li><a href="{{ route('user.notification.all') }}" class="{{ isActive('user.notification.all') }}"><i data-lucide="bell"></i> {{ __('Email & Notifications') }}</a></li>
                    <li><a href="{{ route('user.setting.action') }}" class="{{ isActive('user.setting.action') }}"><i data-lucide="settings"></i> {{ __('Account Closing') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
