<div class="col-xl-3 col-lg-4 col-md-12 col-12">
    <div class="site-card settings-sidebar border-0 shadow-sm" style="background: #f8f9fa; border-radius: 16px;">
        <div class="site-card-body p-3">
            <div class="settings-group mb-4">
                <h6 class="settings-group-title text-dark fw-bold mb-3 px-2" style="font-size: 13px; letter-spacing: 0.5px;">PERSONAL</h6>
                <ul class="settings-nav-list list-unstyled m-0 p-0">
                    <li class="mb-1"><a href="{{ route('user.setting.show') }}" class="d-flex align-items-center gap-3 py-2 px-2 rounded-3 text-decoration-none {{ Request::routeIs('user.setting.show') ? 'bg-white shadow-sm text-primary fw-bold' : 'text-dark fw-500' }}"><i class="fas fa-user small"></i> Profile</a></li>
                    <li class="mb-1"><a href="{{ route('user.setting.security') }}" class="d-flex align-items-center gap-3 py-2 px-2 rounded-3 text-decoration-none {{ Request::routeIs('user.setting.security') ? 'bg-white shadow-sm text-primary fw-bold' : 'text-dark fw-500' }}"><i class="fas fa-lock small"></i> Security</a></li>
                    <li class="mb-1"><a href="{{ route('user.kyc') }}" class="d-flex align-items-center gap-3 py-2 px-2 rounded-3 text-decoration-none {{ Request::routeIs('user.kyc*') ? 'bg-white shadow-sm text-primary fw-bold' : 'text-dark fw-500' }}"><i class="fas fa-id-card small"></i> ID Verification</a></li>
                </ul>
            </div>
            <div class="settings-group">
                <h6 class="settings-group-title text-dark fw-bold mb-3 px-2" style="font-size: 13px; letter-spacing: 0.5px;">ACCOUNTS</h6>
                <ul class="settings-nav-list list-unstyled m-0 p-0">
                    <li class="mb-1"><a href="{{ route('user.accounts') }}" class="d-flex align-items-center gap-3 py-2 px-2 rounded-3 text-decoration-none {{ Request::routeIs('user.accounts') ? 'bg-white shadow-sm text-primary fw-bold' : 'text-dark fw-500' }}"><i class="fas fa-hdd small"></i> Account Settings</a></li>
                    <li class="mb-1"><a href="{{ route('user.notification.all') }}" class="d-flex align-items-center gap-3 py-2 px-2 rounded-3 text-decoration-none {{ Request::routeIs('user.notification.all') ? 'bg-white shadow-sm text-primary fw-bold' : 'text-dark fw-500' }}"><i class="fas fa-bell small"></i> Email & Notifications</a></li>
                    <li class="mb-1"><a href="{{ route('user.setting.action') }}" class="d-flex align-items-center gap-3 py-2 px-2 rounded-3 text-decoration-none {{ Request::routeIs('user.setting.action') ? 'bg-white shadow-sm text-primary fw-bold' : 'text-dark fw-500' }}"><i class="fas fa-cog small"></i> Account Closing</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
