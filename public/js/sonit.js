document.addEventListener('DOMContentLoaded', () => {
    // 1. Live Countdown Clocks
    const clocks = document.querySelectorAll('[data-countdown]');
    const updateClocks = () => {
        const now = Date.now();
        clocks.forEach((clock) => {
            const target = new Date(clock.getAttribute('data-countdown')).getTime();
            const diff = Math.max(0, target - now);

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const mins = Math.floor((diff / (1000 * 60)) % 60);
            const secs = Math.floor((diff / 1000) % 60);

            const setVal = (sel, val) => {
                const el = clock.querySelector(sel);
                if (el) el.textContent = String(val).padStart(2, '0');
            };

            setVal('[data-cd-days]', days);
            setVal('[data-cd-hours]', hours);
            setVal('[data-cd-mins]', mins);
            setVal('[data-cd-secs]', secs);
        });
    };

    if (clocks.length > 0) {
        updateClocks();
        setInterval(updateClocks, 1000);
    }

    // 2. Centralized Student Sidebar Mobile Drawer
    const menuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('portalSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (menuBtn && sidebar && backdrop) {
        const toggleDrawer = () => {
            sidebar.classList.toggle('open');
            backdrop.classList.toggle('open');
        };
        menuBtn.addEventListener('click', toggleDrawer);
        backdrop.addEventListener('click', toggleDrawer);
    }

    // 3. Compact Identity / Overview Tab Switcher
    document.querySelectorAll('[data-tab-group]').forEach((group) => {
        const buttons = group.querySelectorAll('[data-tab-target]');
        const panels = group.querySelectorAll('[data-tab-panel]');
        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-tab-target');
                buttons.forEach((b) => b.classList.toggle('active', b === btn));
                panels.forEach((p) => p.classList.toggle('active', p.getAttribute('data-tab-panel') === target));
            });
        });
    });

    // 4. Copy Account Number / Contact Helper
    document.querySelectorAll('[data-copy-text]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const text = btn.getAttribute('data-copy-text');
            try {
                await navigator.clipboard.writeText(text);
                const orig = btn.textContent;
                btn.textContent = 'Copied';
                setTimeout(() => { btn.textContent = orig; }, 1600);
            } catch (e) {
                // fallback silently
            }
        });
    });

    // 5. Community Reply-To Selection
    document.querySelectorAll('[data-reply-id]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const replyInput = document.getElementById('replyToInput');
            const replyBanner = document.getElementById('replyPreviewBanner');
            const replyText = document.getElementById('replyPreviewText');
            if (replyInput && replyBanner && replyText) {
                replyInput.value = btn.getAttribute('data-reply-id');
                replyText.textContent = btn.getAttribute('data-reply-preview');
                replyBanner.style.display = 'flex';
                const msgBox = document.getElementById('communityMessageInput');
                if (msgBox) msgBox.focus();
            }
        });
    });

    const cancelReply = document.getElementById('cancelReplyBtn');
    if (cancelReply) {
        cancelReply.addEventListener('click', () => {
            const replyInput = document.getElementById('replyToInput');
            const replyBanner = document.getElementById('replyPreviewBanner');
            if (replyInput) replyInput.value = '';
            if (replyBanner) replyBanner.style.display = 'none';
        });
    }

    // 6. Demo Role Quick-Fill on Login Page
    document.querySelectorAll('[data-demo-login]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const idInput = document.getElementById('identifier');
            const passInput = document.getElementById('password');
            if (idInput && passInput) {
                idInput.value = btn.getAttribute('data-demo-id');
                passInput.value = btn.getAttribute('data-demo-pass');
                idInput.focus();
            }
        });
    });

    const chatFeed = document.getElementById('communityChatFeed');
    if (chatFeed) {
        chatFeed.scrollTop = chatFeed.scrollHeight;
    }
});
