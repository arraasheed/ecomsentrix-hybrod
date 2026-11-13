<style>
    .notification-engine-bell { position:relative; }
    .notification-engine-badge { position:absolute; top:-4px; right:-4px; min-width:18px; border-radius:999px; background:#ef4444; color:#fff; font-size:11px; line-height:18px; text-align:center; }
    .notification-engine-menu { min-width:320px; max-width:380px; max-height:420px; overflow:auto; }
    .notification-engine-item { position:relative; padding:10px 34px 10px 12px; border-bottom:1px solid #eef2f7; cursor:pointer; }
    .notification-engine-item.unread { background:#eff6ff; }
    .notification-engine-dismiss { display:none; position:absolute; right:8px; top:8px; border:0; background:transparent; color:#ef4444; }
    .notification-engine-item.dismiss-visible .notification-engine-dismiss { display:block; }
</style>

<script>
(() => {
    if (window.__notificationEngineInitialized) return;
    window.__notificationEngineInitialized = true;

    const endpoint = @json(route('notificationengine.index'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const pollInterval = {{ (int) config('notificationengine.poll_interval', 15000) }};
    let initialized = false;
    let previousOrderIds = new Set();

    const playChime = () => {
        try {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = context.createOscillator();
            const gain = context.createGain();
            oscillator.frequency.value = 880;
            gain.gain.setValueAtTime(0.0001, context.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.12, context.currentTime + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.35);
            oscillator.connect(gain).connect(context.destination);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.35);
        } catch (error) {
            // Browser autoplay policies can reject audio until the user interacts.
        }
    };

    const render = (data) => {
        let bell = document.getElementById('notification-engine-bell');
        if (!bell) {
            const list = document.querySelector('.header-icon-list');
            if (!list) return;
            const item = document.createElement('li');
            item.id = 'notification-engine-bell';
            item.className = 'dropdown dash-h-item notification-engine-bell';
            item.innerHTML = '<a class="dash-head-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" aria-expanded="false" aria-label="Notifications"><i class="ti ti-bell"></i><span class="notification-engine-badge" hidden></span></a><div class="dropdown-menu dropdown-menu-end notification-engine-menu"><div class="notification-engine-items"></div><button type="button" class="dropdown-item text-center notification-engine-clear">Clear All</button></div>';
            list.insertBefore(item, list.firstChild);
            bell = item;
        }

        const badge = bell.querySelector('.notification-engine-badge');
        badge.textContent = data.unread_count;
        badge.hidden = data.unread_count < 1;
        const container = bell.querySelector('.notification-engine-items');
        container.innerHTML = data.notifications.map((notification) => `
            <div class="notification-engine-item ${notification.is_read ? '' : 'unread'}" data-id="${notification.id}" data-target="${notification.target_url || ''}">
                <strong>${escapeHtml(notification.title)}</strong>
                <div>${escapeHtml(notification.message)}</div>
                <small class="text-muted">${escapeHtml(notification.type)}</small>
                <button type="button" class="notification-engine-dismiss" aria-label="Dismiss">×</button>
            </div>
        `).join('') || '<div class="p-3 text-muted">No notifications</div>';

        const currentOrderIds = new Set(data.notifications.filter((item) => item.type === 'order' && !item.is_read).map((item) => String(item.id)));
        if (initialized && document.visibilityState === 'visible' && [...currentOrderIds].some((id) => !previousOrderIds.has(id))) {
            playChime();
        }
        previousOrderIds = currentOrderIds;
        initialized = true;
    };

    const escapeHtml = (value) => {
        const element = document.createElement('div');
        element.textContent = value || '';
        return element.innerHTML;
    };

    const load = () => fetch(endpoint, { headers: { Accept: 'application/json' } }).then((response) => response.json()).then(render);
    document.addEventListener('click', (event) => {
        const item = event.target.closest('.notification-engine-item');
        const dismiss = event.target.closest('.notification-engine-dismiss');
        if (dismiss && item) {
            event.stopPropagation();
            fetch(`${endpoint}/${item.dataset.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } }).then(load);
            return;
        }
        if (item) {
            fetch(`${endpoint}/${item.dataset.id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } })
                .then((response) => response.json())
                .then((data) => { if (data.target_url) window.location.href = data.target_url; else load(); });
        }
        if (event.target.closest('.notification-engine-clear')) {
            fetch(`${endpoint}/clear`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } }).then(load);
        }
    });

    document.addEventListener('pointerdown', (event) => {
        const item = event.target.closest('.notification-engine-item');
        if (!item) return;
        item._dismissTimer = setTimeout(() => item.classList.add('dismiss-visible'), 600);
    });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach((eventName) => {
        document.addEventListener(eventName, (event) => {
            const item = event.target.closest('.notification-engine-item');
            if (item && item._dismissTimer) clearTimeout(item._dismissTimer);
        });
    });
    document.addEventListener('contextmenu', (event) => {
        const item = event.target.closest('.notification-engine-item');
        if (item) {
            event.preventDefault();
            item.classList.add('dismiss-visible');
        }
    });

    load();
    window.setInterval(() => {
        if (document.visibilityState === 'visible') load();
    }, pollInterval);
})();
</script>
