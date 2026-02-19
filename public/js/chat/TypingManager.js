/**
 * TypingManager
 * ─────────────────────────────────────────────
 * Responsibilities:
 *  1. Emit Pusher whisper events when current user types (debounced 800ms)
 *  2. Show "User is typing…" indicator for the OTHER user's typing events
 *  3. Auto-hide typing indicator after 2 seconds of inactivity
 *  4. Never show the indicator to the sender themselves
 *
 * Requires: window.pusher (Pusher JS SDK already connected)
 */
class TypingManager {
    /**
     * @param {object} config
     * @param {number}   config.conversationId   The active conversation ID
     * @param {number}   config.currentUserId    Logged-in user's ID
     * @param {string}   config.inputSelector    CSS selector for the message textarea/input
     * @param {string}   config.indicatorSelector  CSS selector for the typing indicator element
     * @param {object}   config.pusherChannel    A subscribed Pusher private channel instance
     * @param {string}   [config.channelName]    e.g. 'private-chat.42' (used for whisper)
     * @param {number}   [config.debounceMs=800] Debounce delay before re-emitting
     * @param {number}   [config.hideAfterMs=2000] Hide indicator X ms after last event
     */
    constructor(config) {
        this.convId      = config.conversationId;
        this.meId        = config.currentUserId;
        this.input       = document.querySelector(config.inputSelector);
        this.indicator   = document.querySelector(config.indicatorSelector);
        this.channel     = config.pusherChannel;   // Pusher channel object
        this.debounceMs  = config.debounceMs  ?? 800;
        this.hideAfterMs = config.hideAfterMs ?? 2000;

        this._debounceTimer = null;
        this._hideTimer     = null;
        this._lastEmitAt    = 0;

        if (!this.channel) {
            console.warn('[TypingManager] No pusher channel provided.');
            return;
        }

        this._bindInput();
        this._bindInboundTyping();
    }

    /* ─── Private ────────────────────────────────────────── */

    _bindInput() {
        if (!this.input) return;

        this.input.addEventListener('input', () => {
            const now = Date.now();

            // Throttle: emit updates while typing (every 800ms), 
            // instead of waiting until the user STOPS typing.
            if (now - this._lastEmitAt > this.debounceMs) {
                this._emitTyping();
                this._lastEmitAt = now;
            }
        });
    }

    _emitTyping() {
        try {
            // Raw Pusher client event (must start with 'client-')
            // Requires 'Client Events' enabled in Pusher Dashboard
            if (this.channel && typeof this.channel.trigger === 'function') {
                this.channel.trigger('client-typing', {
                    user_id: this.meId,
                });
            }
        } catch (e) {
            console.debug('[TypingManager] trigger failed:', e);
        }
    }

    _bindInboundTyping() {
        // Listen for whisper events coming FROM other users on the channel
        this.channel.listenForWhisper?.('typing', (data) => {
            // Ignore own events (safety guard)
            if (data.user_id == this.meId) return;
            this._showIndicator();
        });

        // Alternative for raw Pusher (without Echo):
        // Pusher client events start with "client-"
        this.channel.bind?.('client-typing', (data) => {
            if (data.user_id == this.meId) return;
            this._showIndicator();
        });
    }

    _showIndicator() {
        if (this.indicator) {
            this.indicator.style.display = '';

            // Scroll into view if near bottom (let ScrollManager handle this via onNewMessage,
            // but we do a basic scroll here as a fallback)
            const area = this.indicator.closest('.msgs-area');
            if (area) {
                const fromBottom = area.scrollHeight - area.scrollTop - area.clientHeight;
                if (fromBottom <= 150) {
                    area.scrollTo({ top: area.scrollHeight, behavior: 'smooth' });
                }
            }
        }

        // Reset auto-hide timer
        clearTimeout(this._hideTimer);
        this._hideTimer = setTimeout(() => {
            this._hideIndicator();
        }, this.hideAfterMs);
    }

    _hideIndicator() {
        if (this.indicator) {
            this.indicator.style.display = 'none';
        }
    }

    /** Call when a new message arrives to hide the indicator immediately */
    onMessageReceived() {
        clearTimeout(this._hideTimer);
        this._hideIndicator();
    }

    /** Clean up timers (call when navigating away / popup closes) */
    destroy() {
        clearTimeout(this._debounceTimer);
        clearTimeout(this._hideTimer);
    }
}

window.TypingManager = TypingManager;
