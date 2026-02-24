/**
 * Mentions Module
 * Handles @ mention autocomplete for post and comment inputs
 */

const Mentions = {
    activeInput: null,
    menu: null,
    query: '',
    selectedIndex: 0,
    users: [],

    init() {
        this.createMenu();
        this.bindEvents();
    },

    createMenu() {
        this.menu = document.createElement('div');
        this.menu.id = 'mentions-menu';
        this.menu.className = 'fixed hidden bg-white rounded-lg shadow-xl border border-gray-200 w-64 z-[100] max-h-64 overflow-y-auto';
        document.body.appendChild(this.menu);
    },

    bindEvents() {
        document.addEventListener('input', (e) => {
            if (e.target.matches('#postContent, .comment-form input[name="content"], #editPostContent, #shareContent')) {
                this.handleInput(e);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (this.menu && !this.menu.classList.contains('hidden')) {
                this.handleKeyDown(e);
            }
        });

        document.addEventListener('click', (e) => {
            if (this.menu && !this.menu.contains(e.target)) {
                this.hideMenu();
            }
        });
    },

    handleInput(e) {
        const input = e.target;
        const cursorPosition = input.selectionStart;
        const textBeforeCursor = input.value.substring(0, cursorPosition);
        
        const atMatch = textBeforeCursor.match(/@(\w*)$/);
        
        if (atMatch) {
            this.activeInput = input;
            this.query = atMatch[1];
            this.showMenu(input, cursorPosition);
            this.fetchUsers(this.query);
        } else {
            this.hideMenu();
        }
    },

    async fetchUsers(query) {
        if (query.length < 1) {
             // Show empty state or clear
             this.renderUsers([]);
             return;
        }

        try {
            const response = await fetch(`/search/suggestions?q=${encodeURIComponent(query)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            this.renderUsers(data.users || []);
        } catch (error) {
            console.error('Mention search error:', error);
        }
    },

    renderUsers(users) {
        this.users = users;
        this.selectedIndex = 0;

        if (users.length === 0) {
            this.menu.innerHTML = '<div class="p-3 text-gray-500 text-sm">No users found</div>';
        } else {
            this.menu.innerHTML = users.map((user, index) => `
                <div class="mention-item flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition-colors ${index === 0 ? 'bg-gray-100' : ''}" data-index="${index}">
                    <img src="${user.avatar_url}" alt="${user.name}" class="w-8 h-8 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate text-sm">${user.name}</p>
                        <p class="text-xs text-gray-500 truncate">@${user.username}</p>
                    </div>
                </div>
            `).join('');

            this.menu.querySelectorAll('.mention-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.selectUser(parseInt(item.dataset.index));
                });
            });
        }
    },

    showMenu(input, cursorPosition) {
        const rect = input.getBoundingClientRect();
        
        // Very basic positioning - in a real app you'd want to calculate cursor coordinates
        this.menu.style.top = `${rect.top + window.scrollY - 10}px`;
        this.menu.style.left = `${rect.left + window.scrollX + (cursorPosition * 8)}px`; // Extremely rough estimate
        
        // Reposition if it goes off screen
        const menuWidth = 256;
        if (rect.left + menuWidth > window.innerWidth) {
            this.menu.style.left = `${window.innerWidth - menuWidth - 20}px`;
        }
        
        // Position above or below
        const menuHeight = 250;
        if (rect.top - menuHeight > 0) {
            this.menu.style.top = `${rect.top + window.scrollY - menuHeight - 10}px`;
        } else {
             this.menu.style.top = `${rect.bottom + window.scrollY + 5}px`;
        }

        this.menu.classList.remove('hidden');
    },

    hideMenu() {
        if (this.menu) {
            this.menu.classList.add('hidden');
        }
    },

    handleKeyDown(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.selectedIndex = (this.selectedIndex + 1) % this.users.length;
            this.updateHighlight();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.selectedIndex = (this.selectedIndex - 1 + this.users.length) % this.users.length;
            this.updateHighlight();
        } else if (e.key === 'Enter' || e.key === 'Tab') {
            if (this.users.length > 0) {
                e.preventDefault();
                this.selectUser(this.selectedIndex);
            }
        } else if (e.key === 'Escape') {
            this.hideMenu();
        }
    },

    updateHighlight() {
        const items = this.menu.querySelectorAll('.mention-item');
        items.forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.classList.add('bg-gray-100');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-gray-100');
            }
        });
    },

    selectUser(index) {
        const user = this.users[index];
        if (!user || !this.activeInput) return;

        const cursorPosition = this.activeInput.selectionStart;
        const text = this.activeInput.value;
        const textBeforeCursor = text.substring(0, cursorPosition);
        const textAfterCursor = text.substring(cursorPosition);

        const newTextBeforeCursor = textBeforeCursor.replace(/@\w*$/, `@${user.username} `);
        
        this.activeInput.value = newTextBeforeCursor + textAfterCursor;
        this.activeInput.focus();
        
        const newCursorPosition = newTextBeforeCursor.length;
        this.activeInput.setSelectionRange(newCursorPosition, newCursorPosition);
        
        this.hideMenu();
    }
};

document.addEventListener('DOMContentLoaded', () => Mentions.init());
