<!-- Ava AI Assistant Widget -->
<div id="ava-widget-container" class="ava-closed">
    <!-- Floating Trigger Button -->
    <button id="ava-trigger" class="ava-pulse-btn" aria-label="Open Ava Assistant">
        <div class="ava-logo-wrapper">
             <i class="fas fa-robot"></i>
        </div>
        <div id="ava-badge-promo" class="d-none d-md-block">Hi! I'm Ava.</div>
    </button>

    <!-- Chat Window -->
    <div id="ava-chat-window" class="glass-morph">
        <div class="ava-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="ava-avatar">
                   <img src="https://ui-avatars.com/api/?name=Ava&background=00549b&color=fff&bold=true" alt="Ava" class="rounded-circle">
                   <div class="ava-online-dot"></div>
                </div>
                <div class="ms-3">
                    <h5 class="m-0 text-white" style="font-size: 16px;">Ava Assistant</h5>
                    <span class="text-white-50" style="font-size: 11px;">Always Online</span>
                </div>
            </div>
            <button id="ava-close" class="btn btn-link text-white p-0">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <div id="ava-messages-container" class="ava-body">
            <!-- Greeting Message -->
            <div class="ava-msg ava-msg-bot">
                <div class="ava-bubble">
                    Hello {{ auth()->user()->first_name }}! I'm your Pinellas FCU assistant. How can I help you today?
                </div>
            </div>
            
            <!-- Default Suggestions -->
            <div class="ava-suggestions">
                <button class="ava-suggest-btn" data-query="What is my total balance?">Check Balance</button>
                <button class="ava-suggest-btn" data-query="How much did I spend this month?">Spending this month</button>
                <button class="ava-suggest-btn" data-query="What was my highest bill?">Highest bill</button>
                <button class="ava-suggest-btn" data-query="My recent activity">Recent activity</button>
            </div>
        </div>

        <div class="ava-footer">
            <form id="ava-query-form" class="d-flex align-items-center">
                @csrf
                <input type="text" id="ava-input" placeholder="Ask Ava something..." autocomplete="off">
                <button type="submit" id="ava-send">
                    <i class="fas fa-paper-plane text-primary"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    #ava-widget-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
        font-family: 'Open Sans', sans-serif;
    }

    #ava-trigger {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #00549b;
        color: white;
        border: none;
        box-shadow: 0 8px 16px rgba(0, 84, 155, 0.4);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    #ava-trigger:hover {
        transform: scale(1.1);
    }

    .ava-pulse-btn::after {
        content: "";
        position: absolute;
        top: -5px; left: -5px; right: -5px; bottom: -5px;
        border-radius: 50%;
        border: 2px solid #00549b;
        opacity: 0.5;
        animation: ava-pulse 2s infinite;
    }

    @keyframes ava-pulse {
        0% { transform: scale(1); opacity: 0.6; }
        100% { transform: scale(1.4); opacity: 0; }
    }

    #ava-badge-promo {
        position: absolute;
        right: 70px;
        background: white;
        color: #00549b;
        padding: 6px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        white-space: nowrap;
        opacity: 0;
        transform: translateX(10px);
        animation: ava-fade-in 0.5s forwards 1.5s;
    }

    @keyframes ava-fade-in {
        to { opacity: 1; transform: translateX(0); }
    }

    #ava-chat-window {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 380px;
        height: 500px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.4);
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        display: none;
        flex-direction: column;
        overflow: hidden;
        transform-origin: bottom right;
        animation: ava-open 0.3s forwards;
    }

    @keyframes ava-open {
        from { transform: scale(0.8) translateY(20px); opacity: 0; }
        to { transform: scale(1) translateY(0); opacity: 1; }
    }

    .ava-header {
        background: #00549b;
        padding: 20px;
        color: white;
    }

    .ava-avatar { position: relative; width: 44px; height: 44px; }
    .ava-avatar img { width: 100%; border: 2px solid rgba(255,255,255,0.5); }
    .ava-online-dot { 
        position: absolute; bottom: 0; right: 0; 
        width: 12px; height: 12px; background: #28a745; 
        border: 2px solid white; border-radius: 50%;
    }

    .ava-body {
        flex-grow: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .ava-msg { display: flex; max-width: 85%; }
    .ava-msg-bot { align-self: flex-start; }
    .ava-msg-user { align-self: flex-end; }

    .ava-bubble {
        padding: 12px 18px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.5;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .ava-msg-bot .ava-bubble { background: #f0f4f8; color: #1a202c; border-bottom-left-radius: 4px; }
    .ava-msg-user .ava-bubble { background: #00549b; color: white; border-bottom-right-radius: 4px; }

    .ava-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }
    .ava-suggest-btn {
        background: white;
        border: 1px solid #00549b;
        color: #00549b;
        padding: 6px 12px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ava-suggest-btn:hover { background: #00549b; color: white; }

    .ava-footer {
        padding: 15px 20px;
        background: white;
        border-top: 1px solid #eee;
    }

    #ava-query-form {
        background: #f8faff;
        border-radius: 24px;
        padding: 4px 16px;
        border: 1px solid #eee;
    }
    #ava-input {
        border: none;
        background: transparent;
        flex-grow: 1;
        padding: 10px 0;
        font-size: 14px;
        outline: none;
    }
    #ava-send {
        background: transparent; border: none; padding: 10px; cursor: pointer;
        transition: transform 0.2s;
    }
    #ava-send:hover { transform: scale(1.1); }

    /* Typing Animation */
    .typing {
        display: flex; align-items: center; gap: 4px;
        padding: 12px 18px; background: #f0f4f8; border-radius: 18px;
        width: fit-content; margin-bottom: 15px;
    }
    .dot { width: 6px; height: 6px; background: #94a3b8; border-radius: 50%; opacity: 0; animation: typing 1s infinite; }
    .dot:nth-child(2) { animation-delay: 0.2s; }
    .dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typing { 0% { opacity: 0; transform: translateY(0); } 50% { opacity: 1; transform: translateY(-3px); } 100% { opacity: 0; transform: translateY(0); } }

    @media (max-width: 576px) {
        #ava-chat-window { width: 90vw; right: -15px; bottom: 85px; }
        #ava-widget-container { right: 20px; bottom: 20px; }
    }
</style>
