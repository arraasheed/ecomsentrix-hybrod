<div
    id="live-chat-widget"
    data-store-id="{{ $storeId ?? getCurrentStore() }}"
    data-init-url="{{ route('livechatsupport.chat.init') }}"
    data-send-url="{{ route('livechatsupport.chat.send') }}"
    style="position:fixed;right:24px;bottom:24px;z-index:1050;font-family:inherit"
>
    <button
        type="button"
        id="live-chat-launcher"
        aria-controls="live-chat-window"
        aria-expanded="false"
        style="border:0;border-radius:999px;background:#2563eb;color:#fff;cursor:pointer;padding:14px 20px;box-shadow:0 8px 24px rgba(0,0,0,.2)"
    >
        Chat with us
    </button>

    <section
        id="live-chat-window"
        hidden
        aria-label="Live chat"
        style="position:absolute;right:0;bottom:64px;width:min(360px,calc(100vw - 32px));overflow:hidden;border:1px solid #e5e7eb;border-radius:16px;background:#fff;box-shadow:0 16px 40px rgba(0,0,0,.2);transform-origin:bottom right"
    >
        <header style="padding:16px;background:#2563eb;color:#fff">
            <strong>Support</strong>
            <div style="font-size:13px;opacity:.85">We usually reply quickly</div>
        </header>
        <div id="live-chat-messages" style="height:280px;overflow:auto;padding:14px;background:#f8fafc"></div>
        <form id="live-chat-form" style="display:flex;gap:8px;padding:12px;border-top:1px solid #e5e7eb">
            @csrf
            <input id="live-chat-input" type="text" maxlength="5000" autocomplete="off" placeholder="Write a message..." required style="min-width:0;flex:1;border:1px solid #d1d5db;border-radius:8px;padding:10px">
            <button type="submit" style="border:0;border-radius:8px;background:#2563eb;color:#fff;padding:0 14px;cursor:pointer">Send</button>
        </form>
    </section>
</div>

<style>
    #live-chat-window { animation: live-chat-open .18s ease-out; }
    @keyframes live-chat-open { from { opacity:0; transform:scale(.96) translateY(8px); } to { opacity:1; transform:scale(1) translateY(0); } }
    .live-chat-message { margin:0 0 8px; padding:8px 10px; border-radius:10px; max-width:85%; white-space:pre-wrap; }
    .live-chat-message--visitor { margin-left:auto; background:#dbeafe; }
    .live-chat-message--bot, .live-chat-message--agent { background:#fff; border:1px solid #e5e7eb; }
</style>

<script>
(() => {
    const widget = document.getElementById('live-chat-widget');
    if (!widget) return;

    const launcher = document.getElementById('live-chat-launcher');
    const windowElement = document.getElementById('live-chat-window');
    const form = document.getElementById('live-chat-form');
    const input = document.getElementById('live-chat-input');
    const messages = document.getElementById('live-chat-messages');
    let conversation = null;

    const appendMessage = (message) => {
        const element = document.createElement('div');
        element.className = `live-chat-message live-chat-message--${message.sender_type}`;
        element.textContent = message.message;
        messages.appendChild(element);
        messages.scrollTop = messages.scrollHeight;
    };

    const initialize = async () => {
        if (conversation) return;

        const response = await fetch(widget.dataset.initUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify({ store_id: widget.dataset.storeId })
        });

        if (!response.ok) throw new Error('Unable to initialize chat.');
        conversation = await response.json();
        appendMessage({ sender_type: 'bot', message: 'Hello! How can we help you today?' });
    };

    launcher.addEventListener('click', async () => {
        const isHidden = windowElement.hidden;
        windowElement.hidden = !isHidden;
        launcher.setAttribute('aria-expanded', String(isHidden));
        if (isHidden) {
            try { await initialize(); } catch (error) { appendMessage({ sender_type: 'bot', message: 'Sorry, chat is temporarily unavailable.' }); }
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message || !conversation) return;

        appendMessage({ sender_type: 'visitor', message });
        input.value = '';

        const response = await fetch(widget.dataset.sendUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify({ conversation_id: conversation.conversation_id, session_token: conversation.session_token, message })
        });

        if (!response.ok) {
            appendMessage({ sender_type: 'bot', message: 'Sorry, we could not send that message.' });
            return;
        }

        const data = await response.json();
        appendMessage(data.message);
    });
})();
</script>
