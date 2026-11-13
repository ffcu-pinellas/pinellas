<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>Registration Complete | Cleveland State Bank</title>

<link rel="apple-touch-icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}">
<link rel="icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" type="image/x-icon">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    min-height: 100vh;
    background-color: #edf0f4;
    background-image: url("{{ asset('assets/institutions/clevelandstatebank/images/csb-bg-ribbons.svg') }}");
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 16px;
    position: relative;
}

.csb-modal-card {
    width: 100%;
    max-width: 680px;
    background: #ffffff;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
    border-radius: 2px;
    overflow: hidden;
}

.modal-header-red {
    background-color: #c8232c;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    position: relative;
}

.modal-header-title {
    color: #ffffff;
    font-size: 13.5px;
    font-weight: bold;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    font-family: Arial, Helvetica, sans-serif;
}

.modal-close-btn {
    position: absolute;
    right: 16px;
    color: #ffffff;
    font-size: 18px;
    font-weight: bold;
    text-decoration: none;
    line-height: 1;
    cursor: pointer;
}

.modal-close-btn:hover {
    opacity: 0.8;
}

.modal-body {
    padding: 36px 40px;
    text-align: center;
}

.csb-welcome-logo {
    width: 170px;
    height: auto;
    max-height: 200px;
    object-fit: contain;
    margin: 0 auto 16px;
    display: block;
}

.modal-divider {
    height: 1px;
    background-color: #e2e8f0;
    margin: 20px 0;
    border: none;
}

.welcome-title {
    font-size: 18px;
    font-weight: bold;
    color: #1e355b;
    margin-bottom: 8px;
    font-family: Arial, Helvetica, sans-serif;
}

.welcome-desc {
    font-size: 13.5px;
    color: #475569;
    line-height: 1.5;
    max-width: 500px;
    margin: 0 auto 20px;
}

.account-details-card {
    background-color: #f8fafc;
    border: 1px solid #cbd5e0;
    border-radius: 4px;
    padding: 18px 24px;
    max-width: 460px;
    margin: 0 auto 24px;
    text-align: left;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 7px 0;
    border-bottom: 1px dashed #e2e8f0;
    font-size: 13px;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: #64748b;
    font-weight: 500;
}

.detail-value {
    color: #1e293b;
    font-weight: bold;
    letter-spacing: 0.5px;
}

.btn-dashboard {
    background-color: #68b828;
    color: #ffffff;
    border: none;
    border-radius: 2px;
    font-size: 13.5px;
    font-weight: bold;
    letter-spacing: 0.8px;
    padding: 14px 36px;
    cursor: pointer;
    text-transform: uppercase;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
}

.btn-dashboard:hover {
    background-color: #559e1d;
}

.step-indicator {
    margin-top: 20px;
    font-size: 11.5px;
    font-weight: bold;
    color: #94a3b8;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
</style>
</head>
<body>

    <div class="csb-modal-card">
        <div class="modal-header-red">
            <span class="modal-header-title">REGISTRATION COMPLETE</span>
            <a href="{{ route('user.dashboard') }}" class="modal-close-btn" title="Dashboard">&#10005;</a>
        </div>

        <div class="modal-body">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/csb-stacked-logo.png') }}" alt="Cleveland State Bank" class="csb-welcome-logo">

            <div class="modal-divider"></div>

            <div class="welcome-title">Welcome to Online Banking!</div>
            <div class="welcome-desc">
                Your online banking account has been successfully created and your digital access is now active.
            </div>

            @if(Auth::check())
            <div class="account-details-card">
                <div class="detail-row">
                    <span class="detail-label">Member Name</span>
                    <span class="detail-value">{{ Auth::user()->full_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Account Number</span>
                    <span class="detail-value"><code>{{ Auth::user()->account_number }}</code></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Routing Number</span>
                    <span class="detail-value"><code>263177742</code></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Access Status</span>
                    <span class="detail-value" style="color: #16a34a;">Active</span>
                </div>
            </div>
            @endif

            <a href="{{ route('user.dashboard') }}" class="btn-dashboard">GO TO ONLINE BANKING DASHBOARD &rarr;</a>

            <div class="step-indicator">STEP 3 OF 3: COMPLETE</div>
        </div>
    </div>

</body>
</html>
