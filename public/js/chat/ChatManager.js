/**
 * ChatManager
 * ─────────────────────────────────────────────
 * The root orchestrator for the full-page chat view (chat/index.blade.php).
 * Wires together ScrollManager + TypingManager and handles:
 *  - Pusher subscription for the active conversation
 *  - Sending messages via AJAX
 *  - Appending messages to the DOM (with dedup)
 *  - Sidebar conversation ordering
 *  - Infinite scroll (load older messages with pagination)
 *  - Conversation search filter
 *  - Read-receipt marking
 *
 * Requires:  window.ScrollManager, window.TypingManager, window.pusher
 */
class ChatManager {
    /**
     * @param {object} config
     * @param {number}       config.currentUserId
     * @param {number|null}  config.activeConversationId
     * @param {string}       config.csrf
     * @param {object}       [config.otherUser]  { id, name, avatar_url }
     */
    constructor(config) {
        this.me           = config.currentUserId;
        this.convId       = config.activeConversationId;
        this.csrf         = config.csrf;
        this.otherUser    = config.otherUser ?? null;

        // Track rendered message IDs to prevent duplicates
        this._rendered = new Set();

        // Managers
        this._scroll  = null;
        this._typing  = null;
        this._channel = null;

        this._esc = (t) => {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(t));
            return d.innerHTML;
        };
        this._fmt = (iso) =>
            new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        // Bootstrap already-rendered message IDs
        document.querySelectorAll('.msg-row[data-mid]').forEach((el) =>
            this._rendered.add(el.dataset.mid)
        );

        this._init();
    }

    /* ──────────────────────────────────────────────────────────────
       Initialisation
    ────────────────────────────────────────────────────────────── */

    _init() {
        this._initScrollManager();
        this._initInputHandlers();
        this._initPusher();
        this._initConvSearch();
        this._initSidebarMobile();
    }

    /* ──────────────────────────────────────────────────────────────
       ScrollManager setup
    ────────────────────────────────────────────────────────────── */

    _initScrollManager() {
        if (!this.convId) return;

        // Determine if there are more pages (blade renders data-has-more attr)
        const area = document.getElementById('msgsArea');
        if (!area) return;

        const hasMore  = area.dataset.hasMore === '1';
        const nextPage = parseInt(area.dataset.nextPage || '2', 10);

        this._scroll = new ScrollManager({
            containerSelector: '#msgsArea',
            nearBottomThreshold: 100,
            onLoadOlder: (page) => this._loadOlderMessages(page),
        });

        this._scroll.setHasMore(hasMore, nextPage - 1); // currentPage = nextPage - 1

        // Scroll to bottom immediately on load
        this._scroll.scrollToBottom(false);
    }

    async _loadOlderMessages(page) {
        const res  = await fetch(
            `/chat/${this.convId}/messages?per_page=30&page=${page}`,
            { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf } }
        );
        const data = await res.json();

        if (!data.success || !data.messages.length) {
            return { html: '', hasMore: false };
        }

        let html = '';
        // API returns newest-first; for older messages we want oldest-first when prepending
        const sorted = data.messages.slice().reverse();
        sorted.forEach((msg) => {
            const id = String(msg.id);
            if (this._rendered.has(id)) return;
            this._rendered.add(id);
            html += this._buildMsgHTML(msg);
        });

        return { html, hasMore: !!data.has_more };
    }

    /* ──────────────────────────────────────────────────────────────
       Message rendering
    ────────────────────────────────────────────────────────────── */

    _buildMsgHTML(msg) {
        const mine   = msg.sender_id == this.me;
        const time   = this._fmt(msg.created_at);
        const avatar = msg.sender?.avatar_url
            ?? this.otherUser?.avatar_url
            ?? 'https://ui-avatars.com/api/?name=U&background=3b82f6&color=fff';
        const readChk = (mine && msg.read_at)
            ? `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                    stroke="rgba(255,255,255,0.7)" stroke-width="2.5">
                   <path d="M4 12l5 5L20 7"/><path d="M9 17l-2-2"/>
               </svg>`
            : '';

        return `
            <div class="msg-row ${mine ? 'mine' : 'other'}" data-mid="${msg.id}">
                ${!mine ? `<img src="${this._esc(avatar)}" alt="" class="msg-sender-img">` : ''}
                <div class="min-w-0">
                    <div class="msg-bubble">${this._esc(msg.body)}</div>
                    <div class="msg-footer">
                        <span class="msg-time">${time}</span>
                        ${readChk}
                    </div>
                </div>
            </div>`;
    }

    _appendMsg(msg) {
        const id = String(msg.id);
        if (this._rendered.has(id)) return;
        this._rendered.add(id);

        const area = document.getElementById('msgsArea');
        if (!area) return;

        const typingEl = document.getElementById('typingIndicator');
        const frag     = document.createRange().createContextualFragment(this._buildMsgHTML(msg));

        if (typingEl) {
            area.insertBefore(frag, typingEl);
        } else {
            area.appendChild(frag);
        }

        // Notify typing manager that a message arrived (hides typing indicator)
        this._typing?.onMessageReceived();

        // Smart scroll
        this._scroll?.onNewMessage(true);

        // Sidebar update
        const isMine = msg.sender_id == this.me;
        this._updateSidebar(this.convId, msg, isMine);
    }

    /* ──────────────────────────────────────────────────────────────
       Input & Send
    ────────────────────────────────────────────────────────────── */

    _initInputHandlers() {
        if (!this.convId) return;

        const input   = document.getElementById('msgInput');
        const sendBtn = document.getElementById('sendMsgBtn');
        if (!input) return;

        // Auto-resize
        input.addEventListener('input', function () {
            this.style.height = '42px';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Enter to send
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this._sendMessage();
            }
        });

        sendBtn?.addEventListener('click', () => this._sendMessage());
    }

    async _sendMessage() {
        if (!this.convId) return;

        const input   = document.getElementById('msgInput');
        const sendBtn = document.getElementById('sendMsgBtn');
        if (!input) return;

        const body = input.value.trim();
        if (!body) return;

        if (sendBtn) sendBtn.disabled = true;
        input.disabled = true;

        try {
            const res  = await fetch(`/chat/${this.convId}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body: JSON.stringify({ body }),
            });
            const data = await res.json();
            if (data.success) {
                input.value      = '';
                input.style.height = '42px';
                this._appendMsg(data.message);
            }
        } catch (e) {
            console.error('[ChatManager] Send failed:', e);
        } finally {
            if (sendBtn) sendBtn.disabled = false;
            input.disabled = false;
            input.focus();
        }
    }

    /* ──────────────────────────────────────────────────────────────
       Pusher
    ────────────────────────────────────────────────────────────── */

    _initPusher() {
        if (!window.pusher || !this.convId) return;

        this._channel = window.pusher.subscribe(`private-chat.${this.convId}`);

        this._channel.bind('pusher:subscription_succeeded', () => {
            console.log(`✅ ChatManager subscribed: private-chat.${this.convId}`);
            // Now that channel is live, set up TypingManager
            this._initTypingManager();
        });

        this._channel.bind('message.sent', (data) => {
            this._appendMsg(data);
            if (data.sender_id != this.me) {
                this._markRead();
            }
        });

        this._channel.bind('pusher:subscription_error', (err) => {
            console.error('[ChatManager] Subscription error:', err);
        });
    }

    /* ──────────────────────────────────────────────────────────────
       TypingManager setup
    ────────────────────────────────────────────────────────────── */

    _initTypingManager() {
        if (!this._channel || !this.convId) return;

        this._typing = new TypingManager({
            conversationId:    this.convId,
            currentUserId:     this.me,
            inputSelector:     '#msgInput',
            indicatorSelector: '#typingIndicator',
            pusherChannel:     this._channel,
            debounceMs:        800,
            hideAfterMs:       2000,
        });
    }

    /* ──────────────────────────────────────────────────────────────
       Mark as read
    ────────────────────────────────────────────────────────────── */

    _markRead() {
        fetch(`/chat/${this.convId}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' },
        }).catch(() => {});
    }

    /* ──────────────────────────────────────────────────────────────
       Sidebar helpers
    ────────────────────────────────────────────────────────────── */

    _updateSidebar(convId, msg, isMine) {
        const preview = document.querySelector(`[data-conv-preview="${convId}"]`);
        const timeEl  = document.querySelector(`[data-conv-time="${convId}"]`);
        const item    = document.querySelector(`.conv-item[data-cid="${convId}"]`);
        const list    = document.getElementById('convList');

        if (preview) {
            preview.innerHTML =
                (isMine ? '<span class="opacity-60">You: </span>' : '') +
                this._esc(msg.body.slice(0, 42));
            preview.classList.remove('has-unread');
        }
        if (timeEl) timeEl.textContent = this._fmt(msg.created_at);
        if (item && list) list.prepend(item);
    }

    /* ──────────────────────────────────────────────────────────────
       Conversation search (sidebar)
    ────────────────────────────────────────────────────────────── */

    _initConvSearch() {
        const search = document.getElementById('convSearch');
        if (!search) return;

        search.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.conv-item').forEach((el) => {
                const name  = (el.dataset.name || '').toLowerCase();
                el.style.display = (!q || name.includes(q)) ? '' : 'none';
            });
        });
    }

    /* ──────────────────────────────────────────────────────────────
       Sidebar mobile toggle
    ────────────────────────────────────────────────────────────── */

    _initSidebarMobile() {
        window.openSidebar = function () {
            document.getElementById('convSidebar')?.classList.add('sidebar-open');
            document.getElementById('sidebarBackdrop')?.classList.add('show');
        };
        window.closeSidebar = function () {
            document.getElementById('convSidebar')?.classList.remove('sidebar-open');
            document.getElementById('sidebarBackdrop')?.classList.remove('show');
        };
    }

    /* ──────────────────────────────────────────────────────────────
       Clean up
    ────────────────────────────────────────────────────────────── */

    destroy() {
        this._typing?.destroy();
        if (this._channel && window.pusher) {
            window.pusher.unsubscribe(`private-chat.${this.convId}`);
        }
    }
}

window.ChatManager = ChatManager;
