<div class="col-xl-3 col-lg-4 col-md-12 col-12 mb-4 mb-lg-0" id="settings-nav-col">
    <div class="site-card settings-sidebar border-0 shadow-sm" style="background: #fff; border-radius: 8px;">
        <div class="site-card-body p-0">
            <div class="settings-group">
                <h6 class="settings-group-title text-muted fw-bold mb-2 px-3 pt-3" style="font-size: 11px; letter-spacing: 0.5px;">PERSONAL</h6>
                <ul class="settings-nav-list list-unstyled m-0 p-0">
                    <li class="mb-0">
                        <a href="javascript:void(0)" onclick="showProfileDetails()" class="d-flex align-items-center gap-3 py-3 px-3 text-decoration-none text-dark fw-500 border-bottom">
                            <i class="far fa-user-circle fs-5 text-muted" style="width: 24px; text-align: center;"></i> 
                            <span>Profile</span>
                        </a>
                    </li>
                    <li class="mb-0">
                        <a href="{{ route('user.setting.security') }}" class="d-flex align-items-center gap-3 py-3 px-3 text-decoration-none text-dark fw-500 border-bottom">
                            <i class="fas fa-shield-alt fs-5 text-muted" style="width: 24px; text-align: center;"></i> 
                            <span>Security</span>
                        </a>
                    </li>
                    <li class="mb-0">
                        <a href="{{ route('user.notification.all') }}" class="d-flex align-items-center gap-3 py-3 px-3 text-decoration-none text-dark fw-500 border-bottom">
                            <i class="far fa-bell fs-5 text-muted" style="width: 24px; text-align: center;"></i> 
                            <span>ALERTS</span>
                        </a>
                    </li>
                    <li class="mb-0">
                        <a href="#" class="d-flex align-items-center gap-3 py-3 px-3 text-decoration-none text-dark fw-500">
                            <i class="fas fa-file-alt fs-5 text-muted" style="width: 24px; text-align: center;"></i> 
                            <span>User agreement</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="settings-group mt-2">
                <h6 class="settings-group-title text-muted fw-bold mb-2 px-3 pt-3" style="font-size: 11px; letter-spacing: 0.5px;">ACCOUNTS</h6>
                <ul class="settings-nav-list list-unstyled m-0 p-0">
                    <li class="mb-0">
                        <a href="{{ route('user.accounts') }}" class="d-flex align-items-center gap-3 py-3 px-3 text-decoration-none text-dark fw-500 border-bottom">
                            <i class="fas fa-university fs-5 text-primary" style="width: 24px; text-align: center;"></i> 
                            <span>Pinellas FCU</span>
                        </a>
                    </li>
                    <li class="mb-0">
                        <a href="#" class="d-flex align-items-center gap-3 py-3 px-3 text-decoration-none text-primary fw-600">
                            <i class="fas fa-plus fs-5" style="width: 24px; text-align: center;"></i> 
                            <span>Add account</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="text-center p-3 mt-4">
                 <p class="small text-muted mb-1">&copy; 2026 Pinellas FCU - Privacy policy</p>
                 <p class="small text-muted mb-1">Federally Insured by NCUA</p>
                 <p class="small text-muted mb-0"><i class="fas fa-home"></i> Equal Housing Lender</p>
            </div>
        </div>
    </div>
</div>
