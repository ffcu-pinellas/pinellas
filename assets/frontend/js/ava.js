document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('ava-trigger');
    const chatWindow = document.getElementById('ava-chat-window');
    const closeBtn = document.getElementById('ava-close');
    const queryForm = document.getElementById('ava-query-form');
    const input = document.getElementById('ava-input');
    const messagesContainer = document.getElementById('ava-messages-container');
    const suggestBtns = document.querySelectorAll('.ava-suggest-btn');

    // Toggle Chat Window
    trigger.addEventListener('click', () => {
        const isHidden = getComputedStyle(chatWindow).display === 'none';
        chatWindow.style.display = isHidden ? 'flex' : 'none';
        if (isHidden) {
            input.focus();
            document.getElementById('ava-badge-promo').style.display = 'none';
        }
    });

    closeBtn.addEventListener('click', () => {
        chatWindow.style.display = 'none';
    });

    // Handle Suggestions
    suggestBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const query = btn.getAttribute('data-query');
            sendQuery(query);
            // Hide suggestions after first click to keep chat clean
            btn.parentElement.style.display = 'none';
        });
    });

    // Handle Form Submission
    queryForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const query = input.value.trim();
        if (query) {
            sendQuery(query);
            input.value = '';
        }
    });

    async function sendQuery(text) {
        // 1. Add User Message
        renderMessage({ type: 'text', message: text }, 'user');

        // 2. Add Typing Indicator
        const typingId = showTyping();
        scrollToBottom();

        try {
            const response = await fetch('/user/ava/query', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ message: text })
            });

            const data = await response.json();
            
            // 3. Remove Typing & Render Response
            removeTyping(typingId);
            renderMessage(data, 'bot');
            
        } catch (error) {
            removeTyping(typingId);
            renderMessage({ type: 'text', message: "I'm sorry, I'm having trouble connecting right now. Please try again later." }, 'bot');
        }

        scrollToBottom();
    }

    function renderMessage(data, side) {
        const msgWrapper = document.createElement('div');
        msgWrapper.className = `ava-msg ava-msg-${side}`;
        
        if (data.type === 'card') {
            let actionsHtml = '';
            if (data.actions) {
                data.actions.forEach(action => {
                    actionsHtml += `<a href="${action.url}" class="ava-btn-action ${action.class || 'btn-primary'}">${action.label}</a>`;
                });
            }

            msgWrapper.innerHTML = `
                <div class="ava-card">
                    <div class="ava-card-title">${data.title}</div>
                    <div class="ava-card-body">${data.message}</div>
                    <div class="ava-card-actions">${actionsHtml}</div>
                </div>
            `;
        } else {
            msgWrapper.innerHTML = `<div class="ava-bubble">${data.message}</div>`;
        }
        
        messagesContainer.appendChild(msgWrapper);
    }

    function showTyping() {
        const id = 'typing-' + Date.now();
        const typingDiv = document.createElement('div');
        typingDiv.id = id;
        typingDiv.className = 'typing';
        typingDiv.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
        messagesContainer.appendChild(typingDiv);
        return id;
    }

    function removeTyping(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
