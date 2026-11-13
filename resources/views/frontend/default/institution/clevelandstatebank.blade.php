<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<title>Cleveland State Bank | Online Banking</title>

<link rel="icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" sizes="32x32" />
<link rel="icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" sizes="192x192" />
<link rel="apple-touch-icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" />

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css?family=Playfair+Display:regular,500,600,700,800,900|Roboto:300,400,500,700,900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<!-- Local CSB Theme Styles -->
<link rel='stylesheet' href='{{ asset('assets/institutions/clevelandstatebank/css/dashicons.css') }}' media='all' />
<link rel='stylesheet' href='{{ asset('assets/institutions/clevelandstatebank/css/megamenu.css') }}' media='all' />
<link rel='stylesheet' href='{{ asset('assets/institutions/clevelandstatebank/css/divi-style.css') }}' media='all' />
<link rel='stylesheet' href='{{ asset('assets/institutions/clevelandstatebank/css/custom.css') }}' media='all' />
<link rel='stylesheet' href='{{ asset('assets/institutions/clevelandstatebank/css/unified-header-footer.css') }}' media='all' />
<link rel='stylesheet' href='{{ asset('assets/institutions/clevelandstatebank/css/unified-page.css') }}' media='all' />
<link rel='stylesheet' href='{{ asset('assets/institutions/clevelandstatebank/css/unified-deferred.css') }}' media='all' />

<style>
/* Robust styling enhancements for standalone display */
body {
    background-color: #ffffff;
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
    color: #333333;
}
.et_pb_section_0_tb_header {
    background: #0d2847 !important;
    color: #ffffff !important;
    padding: 10px 20px !important;
    font-size: 13px !important;
}
.et_pb_section_0_tb_header p {
    margin: 0 !important;
    color: #ffffff !important;
}
.top-menu-cs {
    background: #0f4c81 !important;
    color: #ffffff !important;
    padding: 8px 30px !important;
    font-size: 13px !important;
}
.top-menu-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}
.top-header-left-col {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #ffffff;
}
.top-header-left-col svg {
    height: 18px;
    fill: #ffffff;
    vertical-align: middle;
}
.top-header-right-col {
    display: flex;
    align-items: center;
    gap: 20px;
}
.head-call-btn h4 {
    margin: 0 !important;
    font-size: 11px !important;
    text-transform: uppercase;
    color: #90cdf4 !important;
    letter-spacing: 0.5px;
}
.head-call-btn p {
    margin: 0 !important;
    font-size: 14px !important;
    font-weight: 700;
}
.head-call-btn a {
    color: #ffffff !important;
    text-decoration: none !important;
}
.main-menu-cs {
    background: #ffffff !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    padding: 15px 30px !important;
    position: relative;
    z-index: 100;
}
.main-menu-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}
.csb-nav {
    display: flex;
    align-items: center;
    gap: 30px;
    list-style: none;
    margin: 0;
    padding: 0;
}
.csb-nav > li {
    position: relative;
}
.csb-nav > li > a {
    font-family: 'Roboto', sans-serif;
    font-weight: 500;
    font-size: 15px;
    color: #1a202c;
    text-decoration: none;
    padding: 10px 0;
    display: block;
    transition: color 0.2s;
}
.csb-nav > li > a:hover {
    color: #0f4c81;
}
.csb-sub-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    min-width: 200px;
    border-radius: 6px;
    padding: 10px 0;
    list-style: none;
    z-index: 200;
}
.csb-sub-menu li a {
    display: block;
    padding: 8px 20px;
    color: #2d3748;
    text-decoration: none;
    font-size: 14px;
    transition: background 0.2s, color 0.2s;
}
.csb-sub-menu li a:hover {
    background: #f0f5fa;
    color: #0f4c81;
}
.csb-nav > li:hover .csb-sub-menu {
    display: block;
}

/* Login dropdown button */
.csb-login-wrap {
    position: relative;
}
.csb-login-btn {
    background: #0f4c81;
    color: #ffffff !important;
    padding: 12px 28px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.5px;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    border: none;
    transition: background 0.2s, transform 0.1s;
}
.csb-login-btn:hover {
    background: #0a365c;
}
.csb-login-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.18);
    width: 280px;
    padding: 20px;
    z-index: 300;
    border-top: 3px solid #0f4c81;
}
.csb-login-dropdown.show {
    display: block;
}
.csb-login-dropdown-btn {
    display: block;
    width: 100%;
    text-align: center;
    padding: 12px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none !important;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
    transition: background 0.2s;
}
.csb-btn-blue {
    background: #0f4c81;
    color: #ffffff !important;
}
.csb-btn-blue:hover {
    background: #0a365c;
}
.csb-btn-dark {
    background: #253a7c;
    color: #ffffff !important;
}
.csb-btn-dark:hover {
    background: #182856;
}
.csb-login-dropdown-link {
    display: block;
    text-align: center;
    font-size: 13px;
    color: #0f4c81;
    text-decoration: none;
    margin-top: 5px;
}
.csb-login-dropdown-link:hover {
    text-decoration: underline;
}

/* Hero / Inner Banner */
.inner-banner {
    background: linear-gradient(135deg, #0d2847 0%, #0f4c81 100%) !important;
    padding: 50px 30px !important;
    color: #ffffff !important;
    text-align: center;
}
.inner-banner h1 {
    font-family: 'Playfair Display', serif !important;
    font-size: 42px !important;
    color: #ffffff !important;
    margin: 0 !important;
    font-weight: 700 !important;
}

/* Main Content Area */
.csb-content-wrap {
    max-width: 1000px;
    margin: 50px auto;
    padding: 0 30px;
    font-size: 16px;
    line-height: 1.7;
    color: #4a5568;
}
.csb-content-wrap h4 {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    color: #0f4c81;
    margin-top: 35px;
    margin-bottom: 15px;
}
.csb-content-wrap ul {
    padding-left: 25px;
    margin: 20px 0;
}
.csb-content-wrap ul li {
    margin-bottom: 8px;
}
.csb-login-cta-box {
    background: #f0f5fa;
    border-left: 4px solid #0f4c81;
    padding: 25px 30px;
    border-radius: 0 8px 8px 0;
    margin: 30px 0;
}
.csb-login-cta-box h3 {
    margin-top: 0;
    color: #0f4c81;
    font-size: 22px;
}

/* Footer */
.main-footer {
    background: #0d2847 !important;
    color: #ffffff !important;
    padding: 60px 30px 30px !important;
    font-size: 14px;
}
.footer-row {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 30px;
}
.main-footer h4 {
    color: #ffffff;
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.main-footer a {
    color: #cbd5e0;
    text-decoration: none;
    transition: color 0.2s;
}
.main-footer a:hover {
    color: #ffffff;
}
.bottom-footer {
    background: #081a2e !important;
    color: #a0aec0;
    padding: 20px 30px;
    font-size: 13px;
    border-top: 1px solid rgba(255,255,255,0.08);
}
.bottom-footer-inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

@media (max-width: 900px) {
    .footer-row {
        grid-template-columns: 1fr 1fr;
    }
    .csb-nav {
        display: none;
    }
}
@media (max-width: 600px) {
    .footer-row {
        grid-template-columns: 1fr;
    }
    .top-menu-bar {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    .bottom-footer-inner {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style>
</head>

<body>
<div id="page-container">

    <!-- Top Security Alert Bar -->
    <header>
        <div class="et_pb_section_0_tb_header">
            <div style="max-width:1200px; margin:0 auto; text-align:center;">
                <p>Cleveland State Bank will never contact you and ask for any personal information. This includes your debit card number, online banking password, answers to your security questions, and your full social security number. <br />If you are suspicious, call us directly at 662-843-9461.</p>
            </div>
        </div>

        <!-- Top Header Info & FDIC Bar -->
        <div class="top-menu-cs">
            <div class="top-menu-bar">
                <div class="top-header-left-col">
                    <svg viewBox="0 0 712 300" style="height:18px; fill:#fff;">
                        <title>FDIC logo</title>
                        <polygon points="71.02 67.24 71.02 112.79 148.74 112.79 148.74 172.8 71.02 172.8 71.02 279.8 0.17 279.8 0.17 7.23 156.7 7.23 156.7 67.24 71.02 67.24"></polygon>
                        <path d="M179.88,13.85V286.41h70.51l.34-60V73.85h16.63c45.55,0,77.72,27.48,77.72,76.28,0,53.14-36.15,76.28-78.44,76.28H250.73l-.34,60h30c75.56,0,138.46-59.64,138.46-136.28S356.29,13.85,280.37,13.85Z" transform="translate(-9.88 -6.62)"></path>
                        <rect x="419.03" y="7.23" width="70.85" height="272.57"></rect>
                        <path d="M718,105.31c-14.46-19.53-38-30-62.17-30-43.38,0-72.66,33.26-72.66,75.55,0,43,29.64,74.11,73.74,74.11C680,225,703.16,213.75,718,196v85c-23.49,7.23-40.48,12.65-62.54,12.65-38,0-74.1-14.46-102.3-40.12-30-27.11-43.74-62.54-43.74-103a142.64,142.64,0,0,1,40.13-99.77C576.28,23.25,615,6.62,653.28,6.62c22.77,0,43.74,5.06,64.7,13.73Z" transform="translate(-9.88 -6.62)"></path>
                    </svg>
                    <span><strong>FDIC-Insured</strong> — Backed by the full faith and credit of the U.S. Government</span>
                </div>
                <div class="top-header-right-col">
                    <div class="head-call-btn">
                        <h4>Cleveland</h4>
                        <p><a href="tel:6628439461">662-843-9461</a></p>
                    </div>
                    <div class="head-call-btn">
                        <h4>Grenada</h4>
                        <p><a href="tel:6622275521">662-227-5521</a></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation Bar with CSB Logo -->
        <div class="main-menu-cs">
            <div class="main-menu-bar">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo.png') }}" alt="Cleveland State Bank" style="height: 52px; object-fit: contain;">
                </a>

                <!-- Navigation Links -->
                <ul class="csb-nav">
                    <li>
                        <a href="{{ route('login') }}">Personal <i class="fas fa-chevron-down" style="font-size:11px; margin-left:3px;"></i></a>
                        <ul class="csb-sub-menu">
                            <li><a href="{{ route('login') }}">Checking</a></li>
                            <li><a href="{{ route('login') }}">Savings</a></li>
                            <li><a href="{{ route('login') }}">Loans</a></li>
                            <li><a href="{{ route('login') }}">Debit Cards</a></li>
                            <li><a href="{{ route('login') }}">Online Banking</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{ route('login') }}">Business <i class="fas fa-chevron-down" style="font-size:11px; margin-left:3px;"></i></a>
                        <ul class="csb-sub-menu">
                            <li><a href="{{ route('login') }}">Business Checking</a></li>
                            <li><a href="{{ route('login') }}">Commercial Loans</a></li>
                            <li><a href="{{ route('login') }}">Debit Cards</a></li>
                            <li><a href="{{ route('login') }}">Online Banking</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{ route('login') }}">Services <i class="fas fa-chevron-down" style="font-size:11px; margin-left:3px;"></i></a>
                        <ul class="csb-sub-menu">
                            <li><a href="{{ route('login') }}">Credit Cards</a></li>
                            <li><a href="{{ route('login') }}">Reorder Checks</a></li>
                            <li><a href="{{ route('login') }}">Safe Deposit Boxes</a></li>
                            <li><a href="{{ route('login') }}">Telephone Banking</a></li>
                            <li><a href="{{ route('login') }}">Report Lost Cards</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{ route('login') }}">About <i class="fas fa-chevron-down" style="font-size:11px; margin-left:3px;"></i></a>
                        <ul class="csb-sub-menu">
                            <li><a href="{{ route('login') }}">Our History</a></li>
                            <li><a href="{{ route('login') }}">Locations</a></li>
                            <li><a href="{{ route('login') }}">Loan Officers</a></li>
                            <li><a href="{{ route('login') }}">Contact Us</a></li>
                            <li><a href="{{ route('login') }}">Community Involvement</a></li>
                        </ul>
                    </li>
                </ul>

                <!-- Login Dropdown Button -->
                <div class="csb-login-wrap">
                    <button type="button" class="csb-login-btn" id="csbLoginTrigger">
                        LOGIN <i class="fas fa-lock" style="font-size:12px;"></i>
                    </button>
                    <div class="csb-login-dropdown" id="csbLoginMenu">
                        <a href="{{ route('login') }}" class="csb-login-dropdown-btn csb-btn-blue">ONLINE BANKING LOGIN</a>
                        <a href="{{ route('login') }}" class="csb-login-dropdown-btn csb-btn-dark">CREDIT CARD LOGIN</a>
                        <a href="{{ route('register') }}" class="csb-login-dropdown-link">Enroll in Online Banking</a>
                        <a href="{{ route('login') }}" class="csb-login-dropdown-link">Debit Card Opt-in/out</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Inner Hero Banner -->
    <div class="inner-banner">
        <h1>Online Banking</h1>
    </div>

    <!-- Main Content Section -->
    <div class="csb-content-wrap">
        <!-- Callout Box -->
        <div class="csb-login-cta-box">
            <h3>Online Account Access</h3>
            <p style="margin-bottom: 15px;">Access your accounts securely 24/7. Manage balances, view transactions, initiate transfers, and pay bills anytime.</p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('login') }}" class="csb-login-dropdown-btn csb-btn-blue" style="display:inline-block; width:auto; padding:12px 28px;">LOG IN TO ONLINE BANKING</a>
                <a href="{{ route('register') }}" class="csb-login-dropdown-btn csb-btn-dark" style="display:inline-block; width:auto; padding:12px 28px;">OPEN AN ACCOUNT / ENROLL</a>
            </div>
        </div>

        <p><strong>Cleveland State Bank</strong> offers secure, full-featured online banking to its customers. The service is available 24 hours per day, 7 days per week. To enroll, you can open your account online or talk to a customer service representative at any of our branches.</p>

        <p>Using the Internet, our online banking customers can:</p>
        <ul>
            <li>View real-time checking and savings balances</li>
            <li>View current and posted transaction history</li>
            <li>View check images and deposit receipts</li>
            <li>View electronic statement history</li>
            <li>Transfer funds instantly and make loan payments</li>
            <li>Download transactions to personal finance software</li>
            <li>Manage bill payments with Online Bill Pay</li>
        </ul>

        <h4>Bill Pay</h4>
        <p>Cleveland State Bank also offers an online Bill Pay Service with both electronic and check payments. The online payment process puts flexibility and control in your hands. Electronic payments can be delivered in as little as 3 days and check payments take only 5 days. There is even an innovative “Quick Pay” feature which lets you pay all your bills in 3 clicks.</p>

        <p><strong>Fee Schedule – Bill Pay</strong><br />
        1st three months trial period: unlimited transactions at no charge<br />
        Regular Monthly Fee Schedule: First 10 transactions are free<br />
        More than 10 transactions: .50 cents each</p>

        <p><a href="{{ route('login') }}" style="color:#0f4c81; font-weight:600; text-decoration:underline;">Frequently Asked Questions about eBill-Pay</a></p>

        <div style="margin-top: 40px; padding-top: 25px; border-top: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
            <div>
                <strong style="color:#0f4c81; font-size:18px;">Ready to get started?</strong>
                <p style="margin:0; font-size:14px; color:#718096;">Enroll in minutes and manage your finances securely.</p>
            </div>
            <a href="{{ route('register') }}" class="csb-login-btn" style="background:#0f4c81;">
                ENROLL TODAY <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="footer-row">
            <div>
                <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" alt="Cleveland State Bank" style="height: 48px; object-fit: contain; margin-bottom: 15px;">
                <p style="color:#a0aec0; font-size:13px; line-height:1.6;">Providing trusted community banking with personal care since 1908.</p>
            </div>

            <div>
                <h4>Main Offices</h4>
                <p style="margin-bottom: 12px;">
                    <strong style="color:#ffffff;">CLEVELAND</strong><br />
                    110 Commerce Ave.<br />
                    Cleveland, MS 38732<br />
                    <strong>Phone:</strong> <a href="tel:6628439461">662-843-9461</a><br />
                    <strong>Fax:</strong> 662-846-5824
                </p>
                <p>
                    <strong style="color:#ffffff;">GRENADA</strong><br />
                    229 Sunset Drive<br />
                    Grenada, MS 38901<br />
                    <strong>Phone:</strong> <a href="tel:6622275521">662-227-5521</a><br />
                    <strong>Fax:</strong> 662-227-5524
                </p>
            </div>

            <div>
                <h4>Quick Links</h4>
                <ul style="list-style:none; padding:0; margin:0; line-height:2;">
                    <li><a href="{{ route('login') }}">Online Banking</a></li>
                    <li><a href="{{ route('register') }}">Deposit Accounts</a></li>
                    <li><a href="{{ route('login') }}">Loans & Mortgages</a></li>
                    <li><a href="{{ route('login') }}">Banking Services</a></li>
                    <li><a href="{{ route('login') }}">Our History</a></li>
                </ul>
            </div>

            <div>
                <div style="display:flex; align-items:center; gap:20px; margin-bottom:20px;">
                    <img src="{{ asset('assets/institutions/clevelandstatebank/images/fdic-white.png') }}" alt="Member FDIC" style="height:44px; object-fit:contain;">
                    <img src="{{ asset('assets/institutions/clevelandstatebank/images/ehl-white.png') }}" alt="Equal Housing Lender" style="height:48px; object-fit:contain;">
                </div>
                <p style="color:#a0aec0; font-size:13px; line-height:1.5;">
                    Member FDIC | Equal Housing Lender<br />
                    Routing #: 084301768
                </p>
            </div>
        </div>
    </footer>

    <!-- Bottom Copyright Footer -->
    <div class="bottom-footer">
        <div class="bottom-footer-inner">
            <div>
                &copy; Copyright {{ date('Y') }} Cleveland State Bank. All Rights Reserved.
            </div>
            <div>
                <a href="{{ route('login') }}" style="color:#a0aec0; text-decoration:none; margin-right:15px;">Privacy Policy</a>
                <a href="{{ route('login') }}" style="color:#a0aec0; text-decoration:none;">Equal Opportunity</a>
            </div>
        </div>
    </div>

</div>

<!-- Dropdown toggle script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var trigger = document.getElementById('csbLoginTrigger');
    var menu = document.getElementById('csbLoginMenu');

    if (trigger && menu) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && e.target !== trigger) {
                menu.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>

