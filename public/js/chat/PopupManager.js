/**
 * PopupManager
 * ─────────────────────────────────────────────
 * Responsibilities:
 *  1. Create & track Facebook-style bottom-right chat popups
 *  2. Auto-open when a new message arrives (if user NOT in that conversation)
 *  3. Restore minimized popup if message arrives for it
 *  4. Prevent duplicate popups (same conversation)
 *  5. Support up to 3 concurrent open popups
 *  6. Typing indicator inside popup (via Pusher whisper)
 *  7. Mark-as-read when popup is open and message is received
 *
 * Depends on: window.pusher  (Pusher JS SDK already configured)
 * Exposes:    window.PopupManager (singleton-style)
 */
class PopupManager {
    /**
     * @param {object} config
     * @param {number}  config.currentUserId
     * @param {string}  config.csrf
     * @param {number}  [config.maxPopups=3]
     */
    constructor(config) {
        this.me        = config.currentUserId;
        this.csrf      = config.csrf;
        this.maxPopups = config.maxPopups ?? 3;

        // Map<conversationId, { el, channel, rendered:Set, typingTimer, minimized }>
        this._popups = {};

        // Dock element (should already exist in DOM)
        this._dock = document.getElementById('chatPopupDock');

        // Helpers
        this._esc = (t) => {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(t));
            return d.innerHTML;
        };
        this._fmt = (iso) =>
            new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    /* ──────────────────────────────────────────────────────────────
       Public API
    ────────────────────────────────────────────────────────────── */

    /**
     * Open popup by userId (will create/fetch conversation first).
     * Called from profile page "Message" buttons.
     */
    async openByUserId(userId, userName, userAvatar) {
        try {
            const res  = await fetch('/chat/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body: JSON.stringify({ user_id: userId }),
            });
            const data = await res.json();
            if (!data.success) return;

            this.openByConversation(data.conversation.id, userName, userAvatar);
        } catch (err) {
            console.error('[PopupManager] openByUserId failed:', err);
        }
    }

    /**
     * Open or focus a popup for a known conversation ID.
     * @param {number}  convId
     * @param {string}  userName
     * @param {string}  userAvatar
     * @param {boolean} [autoOpen=false]  Set true when auto-opening from notification
     */
    openByConversation(convId, userName, userAvatar, autoOpen = false) {
        const id = Number(convId);

        // Already open — just restore if minimized & focus input
        if (this._popups[id]) {
            this._restore(id);
            this._focusInput(id);
            return;
        }

        // On the full-page chat view for this conversation — don't open popup
        if (window.CURRENT_CONVERSATION_ID == id) return;

        // Enforce max popup count (hide oldest on overflow)
        this._enforceLimit();

        this._createPopup(id, userName, userAvatar, autoOpen);
    }

    /**
     * Called when a Pusher message arrives on any subscribed channel
     * (global listener set up in ChatManager listens to all conversations).
     */
    onIncomingMessage(convId, msg, userName, userAvatar) {
        const id = Number(convId);

        // If user is on the full-page view for this conversation, skip popup
        if (window.CURRENT_CONVERSATION_ID == id) return;
        // Our own messages don't trigger auto-open
        if (msg.sender_id == this.me) return;

        // If popup is already open, append the message to it
        if (this._popups[id]) {
            this._appendMsg(id, msg, false);
            if (this._popups[id].minimized) this._restore(id);
            this._markRead(id);
            return;
        }

        // Auto-open a new popup
        this.openByConversation(id, userName, userAvatar, true);

        // After the popup is created (async), append the message.
        // We wait a tick for the popup to register in this._popups.
        const checkInterval = setInterval(() => {
            if (this._popups[id]) {
                clearInterval(checkInterval);
                this._appendMsg(id, msg, false);
                this._markRead(id);
            }
        }, 80);

        // Give up after 3 seconds
        setTimeout(() => clearInterval(checkInterval), 3000);
    }

    isOpen(convId) {
        return !!this._popups[Number(convId)];
    }

    /* ──────────────────────────────────────────────────────────────
       Private — Create Popup
    ────────────────────────────────────────────────────────────── */

    _createPopup(convId, userName, userAvatar, autoOpen = false) {
        const esc = this._esc;

        const el = document.createElement('div');
        el.className = 'chat-popup';
        el.id        = `chat-popup-${convId}`;
        el.innerHTML = `
            <div class="popup-header" data-conv="${convId}" onclick="window.ChatPopupEngine.toggle(${convId})">
                <div class="popup-header-left">
                    <div class="popup-avatar-wrap">
                        <img src="${userAvatar || 'https://ui-avatars.com/api/?name=U&background=3b82f6&color=fff'}"
                             alt="${esc(userName)}" class="popup-avatar-img">
                        <span class="popup-online-dot"></span>
                    </div>
                    <div class="popup-header-info">
                        <span class="popup-name">${esc(userName)}</span>
                        <span class="popup-typing-label" id="popup-typing-label-${convId}" style="display:none;">typing…</span>
                    </div>
                </div>
                <div class="popup-header-actions" onclick="event.stopPropagation()">
                    <button class="popup-action-btn" title="Minimise"
                            onclick="window.ChatPopupEngine.toggle(${convId})">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                        </svg>
                    </button>
                    <button class="popup-action-btn" title="Open full chat"
                            onclick="window.location.href='/chat/${convId}'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </button>
                    <button class="popup-action-btn" title="Close"
                            onclick="window.ChatPopupEngine.close(${convId})">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="popup-msgs-${convId}" class="popup-messages" role="log" aria-live="polite"></div>
            <div class="popup-input-bar">
                <input id="popup-input-${convId}" type="text"
                       placeholder="Type a message…"
                       class="popup-input"
                       autocomplete="off"
                       onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();window.ChatPopupEngine.send(${convId});}">
                <button class="popup-send-btn" title="Send"
                        onclick="window.ChatPopupEngine.send(${convId})">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
        `;

        this._dock.appendChild(el);

        this._popups[convId] = {
            el,
            rendered: new Set(),
            channel: null,
            typingTimer: null,
            minimized: false,
        };

        // Load recent messages
        this._loadMessages(convId);

        // Subscribe Pusher
        this._subscribePusher(convId);

        // Attach typing whisper emitter
        this._bindTypingEmitter(convId);

        // Focus
        setTimeout(() => this._focusInput(convId), 200);
    }

    /* ──────────────────────────────────────────────────────────────
       Private — Messages
    ────────────────────────────────────────────────────────────── */

    async _loadMessages(convId) {
        const container = document.getElementById(`popup-msgs-${convId}`);
        if (!container) return;
        container.innerHTML = '<div class="popup-loading">Loading…</div>';

        try {
            const res  = await fetch(`/chat/${convId}/messages?per_page=25`, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf },
            });
            const data = await res.json();

            if (!data.success) {
                container.innerHTML = '<div class="popup-empty">Start the conversation! 👋</div>';
                return;
            }

            container.innerHTML = '';
            // API returns newest-first; reverse to show oldest first
            const msgs = (data.messages || []).slice().reverse();
            msgs.forEach((m) => this._appendMsg(convId, m, m.sender_id == this.me));
            this._scrollToBottom(convId);
        } catch {
            container.innerHTML = '<div class="popup-empty">No messages yet. Say hello! 👋</div>';
        }
    }

    _appendMsg(convId, msg, isMine) {
        const state = this._popups[convId];
        if (!state) return;

        const id = String(msg.id);
        if (state.rendered.has(id)) return;
        state.rendered.add(id);

        // Hide typing indicator when a message arrives
        this._hideTyping(convId);

        const html = `
            <div class="popup-msg ${isMine ? 'mine' : 'other'}">
                <div class="popup-bubble">${this._esc(msg.body)}</div>
                <div class="popup-time">${this._fmt(msg.created_at)}</div>
            </div>`;

        const container = document.getElementById(`popup-msgs-${convId}`);
        if (container) {
            container.insertAdjacentHTML('beforeend', html);
            this._scrollToBottom(convId);
        }
    }

    _scrollToBottom(convId) {
        const el = document.getElementById(`popup-msgs-${convId}`);
        if (el) el.scrollTop = el.scrollHeight;
    }

    /* ──────────────────────────────────────────────────────────────
       Private — Pusher
    ────────────────────────────────────────────────────────────── */

    _subscribePusher(convId) {
        if (typeof window.pusher === 'undefined') return;

        const channel = window.pusher.subscribe(`private-chat.${convId}`);
        channel.bind('message.sent', (data) => {
            const isMine = data.sender_id == this.me;
            this._appendMsg(convId, data, isMine);
            if (!isMine) this._markRead(convId);
            // Update sidebar conversation order
            this._updateSidebarItem(convId, data, isMine);
        });

        // Listen for typing whisper from other user
        channel.bind('client-typing', (data) => {
            if (data.user_id == this.me) return;
            this._showTyping(convId);
        });

        this._popups[convId].channel = channel;
    }

    /* ──────────────────────────────────────────────────────────────
       Private — Typing
    ────────────────────────────────────────────────────────────── */

    _bindTypingEmitter(convId) {
        const input = document.getElementById(`popup-input-${convId}`);
        if (!input) return;

        let lastEmit  = 0;
        let debounce  = null;
        const DEBOUNCE = 800;

        input.addEventListener('input', () => {
            const now = Date.now();
            clearTimeout(debounce);
            if (now - lastEmit < DEBOUNCE) {
                debounce = setTimeout(() => this._emitTyping(convId), DEBOUNCE);
            } else {
                this._emitTyping(convId);
                lastEmit = now;
            }
        });
    }

    _emitTyping(convId) {
        try {
            const state = this._popups[convId];
            if (!state?.channel) return;
            state.channel.trigger('client-typing', { user_id: this.me });
        } catch (e) {
            /* ignore */
        }
    }

    _showTyping(convId) {
        const label = document.getElementById(`popup-typing-label-${convId}`);
        if (label) label.style.display = 'block';

        clearTimeout(this._popups[convId]?.typingTimer);
        if (this._popups[convId]) {
            this._popups[convId].typingTimer = setTimeout(
                () => this._hideTyping(convId),
                2000
            );
        }
    }

    _hideTyping(convId) {
        const label = document.getElementById(`popup-typing-label-${convId}`);
        if (label) label.style.display = 'none';
    }

    /* ──────────────────────────────────────────────────────────────
       Private — Controls
    ────────────────────────────────────────────────────────────── */

    toggle(convId) {
        const el    = document.getElementById(`chat-popup-${convId}`);
        const state = this._popups[convId];
        if (!el || !state) return;

        state.minimized = !state.minimized;
        el.classList.toggle('minimized', state.minimized);
    }

    _restore(convId) {
        const el    = document.getElementById(`chat-popup-${convId}`);
        const state = this._popups[convId];
        if (state && state.minimized) {
            state.minimized = false;
            el?.classList.remove('minimized');
        }
    }

    close(convId) {
        const state = this._popups[convId];
        if (!state) return;

        clearTimeout(state.typingTimer);

        if (state.channel && typeof window.pusher !== 'undefined') {
            window.pusher.unsubscribe(`private-chat.${convId}`);
        }

        state.el.remove();
        delete this._popups[convId];
    }

    _focusInput(convId) {
        document.getElementById(`popup-input-${convId}`)?.focus();
    }

    _enforceLimit() {
        const ids = Object.keys(this._popups);
        if (ids.length >= this.maxPopups) {
            // Close the oldest (first) popup
            this.close(Number(ids[0]));
        }
    }

    /* ──────────────────────────────────────────────────────────────
       Private — Send Message
    ────────────────────────────────────────────────────────────── */

    async send(convId) {
        const input = document.getElementById(`popup-input-${convId}`);
        if (!input) return;

        const body = input.value.trim();
        if (!body) return;

        input.value    = '';
        input.disabled = true;

        try {
            const res  = await fetch(`/chat/${convId}/send`, {
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
                this._appendMsg(convId, data.message, true);
                this._updateSidebarItem(convId, data.message, true);
            }
        } catch (err) {
            console.error('[PopupManager] send failed:', err);
        } finally {
            input.disabled = false;
            input.focus();
        }
    }

    /* ──────────────────────────────────────────────────────────────
       Private — Sidebar updates
    ────────────────────────────────────────────────────────────── */

    _updateSidebarItem(convId, msg, isMine) {
        const preview = document.querySelector(`[data-conv-preview="${convId}"]`);
        const timeEl  = document.querySelector(`[data-conv-time="${convId}"]`);
        const item    = document.querySelector(`.conv-item[data-cid="${convId}"]`);
        const list    = document.getElementById('convList');

        if (preview) {
            preview.innerHTML = (isMine ? '<span class="opacity-60">You: </span>' : '') +
                this._esc(msg.body.slice(0, 45));
        }
        if (timeEl) {
            timeEl.textContent = this._fmt(msg.created_at);
        }
        if (item && list) {
            list.prepend(item);
        }
    }

    _markRead(convId) {
        fetch(`/chat/${convId}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' },
        }).catch(() => {});
    }
}

window.PopupManager = PopupManager;
