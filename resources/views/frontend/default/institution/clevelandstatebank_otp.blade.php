<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>Security Verification | Cleveland State Bank</title>

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
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}

.csb-fdic-topbar {
    background-color: #ffffff;
    padding: 6px 20px;
    font-size: 11px;
    color: #1a2d54;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid #edf0f4;
    width: 100%;
}

.csb-fdic-badge {
    font-weight: 900;
    font-size: 12.5px;
    color: #0b2246;
    letter-spacing: 0.5px;
}

.card-center-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 16px;
}

.csb-modal-card {
    width: 100%;
    max-width: 540px;
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
}

.modal-header-title {
    color: #ffffff;
    font-size: 13.5px;
    font-weight: bold;
    letter-spacing: 0.8px;
    text-transform: uppercase;
}

.modal-body {
    padding: 36px 32px 40px;
    text-align: center;
}

.csb-welcome-logo {
    width: 150px;
    height: auto;
    max-height: 180px;
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
    font-size: 17px;
    font-weight: bold;
    color: #222222;
    margin-bottom: 8px;
}

.welcome-desc {
    font-size: 13.5px;
    color: #4a5568;
    line-height: 1.5;
    margin-bottom: 24px;
}

.input-otp {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin: 25px 0;
}

.inputotp {
    width: 54px;
    height: 58px;
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    border: 1px solid #b0bec5;
    border-radius: 2px;
    outline: none;
    transition: border-color 0.2s;
    font-family: inherit;
}

.inputotp:focus {
    border-color: #c8232c;
    box-shadow: 0 0 0 2px rgba(200, 35, 44, 0.15);
}

.inputotp:disabled {
    background-color: #f8fafc;
    cursor: not-allowed;
}

.btn-started {
    background-color: #c8232c;
    color: #ffffff;
    border: none;
    border-radius: 2px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.8px;
    padding: 13px 32px;
    cursor: pointer;
    text-transform: uppercase;
    width: 100%;
}

.btn-started:hover {
    background-color: #a81c24;
}

.btn-resend {
    color: #1b325f;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.btn-resend:hover {
    text-decoration: underline;
}

.csb-footer {
    text-align: center;
    padding: 20px;
    font-size: 11.5px;
    color: #64748b;
}

.csb-footer a {
    color: #475569;
    text-decoration: none;
}
</style>
</head>
<body>

    <div class="csb-fdic-topbar">
        <span class="csb-fdic-badge">FDIC</span>
        <span>FDIC-Insured — Backed by the full faith and credit of the U.S. Government</span>
    </div>

    <div class="card-center-wrapper">
        <div class="csb-modal-card">
            <div class="modal-header-red">
                <span class="modal-header-title">SECURITY VERIFICATION</span>
            </div>

            <div class="modal-body">
                <img src="{{ asset('assets/institutions/clevelandstatebank/images/csb-stacked-logo.png') }}" alt="Cleveland State Bank / Grenada State Bank" class="csb-welcome-logo">

                <hr class="modal-divider">

                <div class="welcome-title">Enter Verification Code</div>
                <div class="welcome-desc">
                    A secure 4-digit verification code has been dispatched to <strong>{{ auth()->user()->phone ?? 'your mobile device' }}</strong>.<br>
                    Expires in: <span id="otptimer" style="color: #c8232c; font-weight: bold;"></span>
                </div>

                <form action="{{ route('otp.verify.post') }}" method="POST">
                    @csrf
                    <input type="hidden" name="phone" value="{{ auth()->user()->phone }}">
                    <div class="input-otp">
                        <input class="inputotp" name="otp[]" type="number" maxlength="1" required autofocus/>
                        <input class="inputotp" name="otp[]" type="number" maxlength="1" disabled required/>
                        <input class="inputotp" name="otp[]" type="number" maxlength="1" disabled required/>
                        <input class="inputotp" name="otp[]" type="number" maxlength="1" disabled required/>
                    </div>

                    <button type="submit" id="btn-verify" class="btn-started mb-3">
                        VERIFY &amp; PROCEED
                    </button>

                    <div style="margin-top: 16px; font-size: 13px; color: #64748b;">
                        Did not receive code? <a href="{{ route('otp.resend') }}" class="btn-resend">Resend Code</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="csb-footer">
        <div>
            &copy; {{ date('Y') }} Cleveland State Bank &bull; 662-843-9461 &bull; <a href="#">Privacy Policy</a> &bull; Member FDIC &bull; Equal Housing Lender
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll(".inputotp");
        inputs.forEach((input, index) => {
            input.addEventListener("keyup", (e) => {
                const currentInput = input;
                const nextInput = input.nextElementSibling;
                const prevInput = input.previousElementSibling;

                if (currentInput.value.length > 1) {
                    currentInput.value = currentInput.value.slice(0, 1);
                }

                if (nextInput && currentInput.value !== "") {
                    nextInput.removeAttribute("disabled");
                    nextInput.focus();
                }

                if (e.key === "Backspace") {
                    if (prevInput) {
                        currentInput.setAttribute("disabled", true);
                        currentInput.value = "";
                        prevInput.focus();
                    }
                }
            });
        });

        let remaining = 120;
        function timer() {
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;
            const timerEl = document.getElementById('otptimer');
            if (timerEl) timerEl.innerHTML = m + ':' + s;
            remaining -= 1;
            if (remaining >= 0) {
                setTimeout(timer, 1000);
            }
        }
        timer();
    });
    </script>
</body>
</html>

