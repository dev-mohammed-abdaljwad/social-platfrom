/**
 * ScrollManager
 * ─────────────────────────────────────────────
 * Responsibilities:
 *  1. Smart auto-scroll (only when near bottom)
 *  2. Infinite scroll — load older messages on scroll-to-top
 *  3. "New message" indicator when user has scrolled up
 *  4. Scroll position preservation after prepending old messages
 */
class ScrollManager {
    /**
     * @param {object} config
     * @param {string}   config.containerSelector  CSS selector for the scrollable area
     * @param {number}   [config.nearBottomThreshold=100]  px from bottom to be considered "near"
     * @param {Function} config.onLoadOlder  async fn(page) → { html, hasMore }
     *   Called when user scrolls to top and more pages exist.
     *   Must resolve with:
     *     html    – innerHTML fragment to prepend (oldest messages first)
     *     hasMore – boolean, whether even more pages exist
     */
    constructor(config) {
        this.container        = document.querySelector(config.containerSelector);
        this.nearBottomPx     = config.nearBottomThreshold ?? 100;
        this.onLoadOlder      = config.onLoadOlder;

        if (!this.container) return;

        // State
        this._currentPage  = 1;
        this._hasMore      = false; // will be set by caller via setHasMore()
        this._loading      = false;
        this._indicator    = null;

        this._buildNewMsgIndicator();
        this._bindScroll();
    }

    /* ─── Public API ─────────────────────────────────────── */

    /** Set whether older pages exist (call on init and after each load). */
    setHasMore(value, nextPage) {
        this._hasMore    = !!value;
        this._currentPage = nextPage ?? this._currentPage;
    }

    /** Returns true if the user is near the bottom. */
    isNearBottom() {
        if (!this.container) return true;
        const { scrollTop, scrollHeight, clientHeight } = this.container;
        return (scrollHeight - scrollTop - clientHeight) <= this.nearBottomPx;
    }

    /**
     * Scroll to bottom.
     * @param {boolean} smooth  – use smooth behaviour (default: false = instant)
     */
    scrollToBottom(smooth = false) {
        if (!this.container) return;
        this.container.scrollTo({
            top: this.container.scrollHeight,
            behavior: smooth ? 'smooth' : 'instant',
        });
    }

    /**
     * Call when a new message arrives (not from load-older).
     * → scrolls if near bottom, shows indicator otherwise.
     */
    onNewMessage(smooth = true) {
        if (this.isNearBottom()) {
            this.scrollToBottom(smooth);
            this._hideIndicator();
        } else {
            this._showIndicator();
        }
    }

    /* ─── Private ────────────────────────────────────────── */

    _buildNewMsgIndicator() {
        const btn = document.createElement('button');
        btn.id        = 'newMsgIndicator';
        btn.innerHTML = '↓ New message';
        btn.style.cssText = `
            display:none; position:absolute; bottom:72px; left:50%; transform:translateX(-50%);
            background:linear-gradient(135deg,#2563eb,#4f46e5); color:#fff; border:none;
            border-radius:20px; padding:6px 16px; font-size:12px; font-weight:600;
            cursor:pointer; box-shadow:0 4px 14px rgba(37,99,235,.4); z-index:10;
            white-space:nowrap; transition:opacity .2s;
        `;
        btn.addEventListener('click', () => {
            this.scrollToBottom(true);
            this._hideIndicator();
        });

        // Parent must be position:relative for absolute positioning
        const parent = this.container.parentElement;
        if (parent) {
            parent.style.position = 'relative';
            parent.appendChild(btn);
        }
        this._indicator = btn;
    }

    _showIndicator() {
        if (this._indicator) this._indicator.style.display = 'block';
    }

    _hideIndicator() {
        if (this._indicator) this._indicator.style.display = 'none';
    }

    _bindScroll() {
        this.container.addEventListener('scroll', () => {
            // Hide indicator when user scrolls to bottom
            if (this.isNearBottom()) this._hideIndicator();

            // Infinite scroll trigger: when within 50px of top
            if (this.container.scrollTop <= 50) {
                this._loadOlderMessages();
            }
        }, { passive: true });
    }

    async _loadOlderMessages() {
        if (this._loading || !this._hasMore || !this.onLoadOlder) return;
        this._loading = true;

        // Show a subtle loading indicator at the top
        const spinner = this._insertTopSpinner();

        try {
            const nextPage = this._currentPage + 1;
            const result   = await this.onLoadOlder(nextPage);

            if (!result) return;

            // Preserve scroll position BEFORE prepending
            const prevHeight = this.container.scrollHeight;

            // Prepend HTML
            this.container.insertAdjacentHTML('afterbegin', result.html);

            // Restore scroll so the user stays at the same visual position
            this.container.scrollTop += (this.container.scrollHeight - prevHeight);

            this._currentPage = nextPage;
            this.setHasMore(result.hasMore, nextPage);
        } catch (err) {
            console.error('[ScrollManager] Load older failed:', err);
        } finally {
            this._loading = false;
            spinner.remove();
        }
    }

    _insertTopSpinner() {
        const div = document.createElement('div');
        div.id = 'oldMsgsSpinner';
        div.style.cssText = 'text-align:center;padding:10px;font-size:12px;color:#9ca3af;';
        div.textContent = '⏳ Loading older messages…';
        this.container.insertAdjacentElement('afterbegin', div);
        return div;
    }
}

window.ScrollManager = ScrollManager;
