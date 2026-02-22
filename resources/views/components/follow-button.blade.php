{{--
Follow Button Component
Usage:
@include('components.follow-button', ['targetUser' => $user, 'followStatus' => 'none|pending|accepted'])

followStatus values:
'none' → show Follow button
'pending' → show Requested button + cancel
'accepted' → show Following button (hover: Unfollow)
--}}

@php
    $targetId = $targetUser->id;
    $status = $followStatus ?? 'none'; // none | pending | accepted
    $isOwn = auth()->check() && auth()->id() === $targetId;
    $csrf = csrf_token();
@endphp

@auth
    @if (!$isOwn)
        <div id="follow-btn-wrapper-{{ $targetId }}" class="inline-flex gap-2">

            {{-- ▸ NOT FOLLOWING --}}
            @if ($status === 'none')
                <button id="follow-btn-{{ $targetId }}" onclick="handleFollowAction({{ $targetId }}, 'follow')"
                    class="follow-action-btn inline-flex items-center gap-2 px-5 py-2 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Follow
                </button>

                {{-- ▸ PENDING REQUEST --}}
            @elseif ($status === 'pending')
                <button id="follow-btn-{{ $targetId }}" onclick="handleFollowAction({{ $targetId }}, 'cancel')"
                    class="follow-action-btn inline-flex items-center gap-2 px-5 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-semibold hover:bg-red-50 hover:text-red-600 hover:border-red-200 border border-gray-300 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="btn-label">Requested</span>
                </button>

                {{-- ▸ FOLLOWING --}}
            @elseif ($status === 'accepted')
                <button id="follow-btn-{{ $targetId }}" onclick="handleFollowAction({{ $targetId }}, 'unfollow')"
                    class="follow-action-btn inline-flex items-center gap-2 px-5 py-2 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-sm font-semibold hover:bg-red-50 hover:text-red-600 hover:border-red-200 active:scale-95 transition-all group">
                    <svg class="w-4 h-4 group-hover:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg class="w-4 h-4 hidden group-hover:block text-red-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="group-hover:hidden">Following</span>
                    <span class="hidden group-hover:inline">Unfollow</span>
                </button>
            @endif

        </div>

        {{-- Toast notification slot --}}
        <div id="follow-toast-{{ $targetId }}"
            class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-[200] px-5 py-3 rounded-xl shadow-xl text-white text-sm font-medium transition-all">
        </div>
    @endif
@endauth

<script>
    // Guard: only define once
    if (typeof window.__followHandlerDefined === 'undefined') {
        window.__followHandlerDefined = true;

        window.handleFollowAction = async function (userId, action) {
            const btn = document.getElementById(`follow-btn-${userId}`);
            const wrapper = document.getElementById(`follow-btn-wrapper-${userId}`);
            const toast = document.getElementById(`follow-toast-${userId}`);
            if (!btn) return;

            // Disable button while working
            btn.disabled = true;
            btn.style.opacity = '0.6';

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfMeta ? csrfMeta.content : '';

            let url, method;

            switch (action) {
                case 'follow':
                    url = `/follow/${userId}`;
                    method = 'POST';
                    break;
                case 'unfollow':
                    url = `/follow/${userId}`;
                    method = 'DELETE';
                    break;
                case 'cancel':
                    url = `/follow-requests/${userId}/cancel`;
                    method = 'DELETE';
                    break;
            }

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });
                const data = await res.json();

                if (data.success) {
                    showFollowToast(userId, data.message, 'success');

                    // Derive new status
                    let newStatus = 'none';
                    if (action === 'follow' && data.follow) {
                        newStatus = data.follow.status === 'accepted' ? 'accepted' : 'pending';
                    } else if (action === 'follow' && data.message.includes('pending')) {
                        newStatus = 'pending';
                    } else if (action === 'follow') {
                        newStatus = 'accepted';
                    }
                    // unfollow → none, cancel → none
                    updateFollowButton(userId, newStatus);
                } else {
                    showFollowToast(userId, data.message || 'Something went wrong.', 'error');
                    btn.disabled = false;
                    btn.style.opacity = '';
                }
            } catch (e) {
                showFollowToast(userId, 'Network error. Please try again.', 'error');
                btn.disabled = false;
                btn.style.opacity = '';
            }
        };

        window.updateFollowButton = function (userId, status) {
            const wrapper = document.getElementById(`follow-btn-wrapper-${userId}`);
            if (!wrapper) return;

            const templates = {
                none: `
                <button id="follow-btn-${userId}"
                    onclick="handleFollowAction(${userId}, 'follow')"
                    class="follow-action-btn inline-flex items-center gap-2 px-5 py-2 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Follow
                </button>`,
                pending: `
                <button id="follow-btn-${userId}"
                    onclick="handleFollowAction(${userId}, 'cancel')"
                    class="follow-action-btn inline-flex items-center gap-2 px-5 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-semibold hover:bg-red-50 hover:text-red-600 hover:border-red-200 border border-gray-300 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Requested
                </button>`,
                accepted: `
                <button id="follow-btn-${userId}"
                    onclick="handleFollowAction(${userId}, 'unfollow')"
                    class="follow-action-btn inline-flex items-center gap-2 px-5 py-2 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-sm font-semibold hover:bg-red-50 hover:text-red-600 hover:border-red-200 active:scale-95 transition-all group">
                    <svg class="w-4 h-4 group-hover:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg class="w-4 h-4 hidden group-hover:block text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span class="group-hover:hidden">Following</span>
                    <span class="hidden group-hover:inline">Unfollow</span>
                </button>`,
            };

            wrapper.innerHTML = templates[status] ?? templates.none;
        };

        window.showFollowToast = function (userId, message, type) {
            const toast = document.getElementById(`follow-toast-${userId}`);
            if (!toast) return;

            toast.textContent = message;
            toast.className = `fixed bottom-6 left-1/2 -translate-x-1/2 z-[200] px-5 py-3 rounded-xl shadow-xl text-white text-sm font-medium transition-all
            ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
            toast.classList.remove('hidden');

            setTimeout(() => { toast.classList.add('hidden'); }, 3000);
        };
    }
</script>