@extends('layouts.app')

@section('title', ($user->name ?? 'User') . ' — Followers & Following · SocialTime')

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- ── Profile Mini-Header ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-5 flex items-center gap-4">
            <a href="{{ route('profile.show', $user->id) }}">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                    class="w-16 h-16 rounded-full object-cover border-2 border-blue-100 shadow">
            </a>
            <div class="flex-1 min-w-0">
                <h1 class="text-xl font-bold text-gray-900 truncate">{{ $user->name }}</h1>
                <p class="text-sm text-gray-500">{{ '@' . $user->username }}</p>
            </div>
            <a href="{{ route('profile.show', $user->id) }}"
                class="text-sm text-blue-600 hover:underline font-medium whitespace-nowrap">
                View Profile →
            </a>
        </div>

        {{-- ── Tabs ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-5 overflow-hidden">
            <div class="flex border-b border-gray-100">
                <button data-tab="followers" class="tab-btn flex-1 relative px-4 py-3.5 text-sm font-semibold text-center transition-colors
                           {{ $tab === 'followers' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    <span class="flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Followers
                        @if(method_exists($followers, 'total') && $followers->total() > 0)
                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-bold">
                                {{ $followers->total() }}
                            </span>
                        @endif
                    </span>
                    @if($tab === 'followers')
                        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 rounded-t"></span>
                    @endif
                </button>

                <button data-tab="following" class="tab-btn flex-1 relative px-4 py-3.5 text-sm font-semibold text-center transition-colors
                           {{ $tab === 'following' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    <span class="flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Following
                        @if(method_exists($following, 'total') && $following->total() > 0)
                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-bold">
                                {{ $following->total() }}
                            </span>
                        @endif
                    </span>
                    @if($tab === 'following')
                        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 rounded-t"></span>
                    @endif
                </button>

                @auth
                    @if(auth()->id() === $user->id)
                        <button data-tab="requests" class="tab-btn flex-1 relative px-4 py-3.5 text-sm font-semibold text-center transition-colors
                                       {{ $tab === 'requests' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Follow Requests
                                @if(method_exists($requests, 'total') && $requests->total() > 0)
                                    <span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-xs rounded-full font-bold">
                                        {{ $requests->total() }}
                                    </span>
                                @endif
                            </span>
                            @if($tab === 'requests')
                                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 rounded-t"></span>
                            @endif
                        </button>
                    @endif
                @endauth
            </div>
        </div>

        {{-- ── Followers Tab ── --}}
        <div id="followersTab" class="tab-panel {{ $tab !== 'followers' ? 'hidden' : '' }} space-y-3">
            @php $followerItems = $followers instanceof \Illuminate\Contracts\Pagination\Paginator ? $followers->items() : ($followers instanceof \Illuminate\Support\Collection ? $followers->all() : []); @endphp
            @forelse($followerItems as $follow)
                @php $person = $follow->follower; @endphp
                @if($person)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-4 hover:shadow-md transition-shadow"
                        id="follower-{{ $person->id }}">
                        <a href="{{ route('profile.show', $person->id) }}" class="flex-shrink-0">
                            <img src="{{ $person->avatar_url }}" alt="{{ $person->name }}"
                                class="w-13 h-13 w-12 h-12 rounded-full object-cover border border-gray-100 shadow-sm">
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('profile.show', $person->id) }}"
                                class="font-semibold text-gray-900 hover:text-blue-600 transition-colors truncate block">
                                {{ $person->name }}
                            </a>
                            <p class="text-sm text-gray-500 truncate">{{ '@' . $person->username }}</p>
                        </div>
                        @auth
                            @if(auth()->id() !== $person->id)
                                @php
                                    $myFollowStatus = \App\Models\Follow::where('follower_id', auth()->id())
                                        ->where('followee_id', $person->id)
                                        ->first()?->status?->value ?? 'none';
                                @endphp
                                @include('components.follow-button', ['targetUser' => $person, 'followStatus' => $myFollowStatus])
                            @endif
                        @endauth
                    </div>
                @endif
            @empty
                <div class="bg-white rounded-xl border border-gray-200 p-10 text-center">
                    <svg class="w-14 h-14 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-gray-500 font-medium">No followers yet</p>
                    <p class="text-sm text-gray-400 mt-1">When someone follows this account, they'll appear here.</p>
                </div>
            @endforelse

            {{-- Pagination --}}
            @if(method_exists($followers, 'hasPages') && $followers->hasPages())
                <div class="pt-2">{{ $followers->appends(['tab' => 'followers'])->links() }}</div>
            @endif
        </div>

        {{-- ── Following Tab ── --}}
        <div id="followingTab" class="tab-panel {{ $tab !== 'following' ? 'hidden' : '' }} space-y-3">
            @php $followingItems = $following instanceof \Illuminate\Contracts\Pagination\Paginator ? $following->items() : ($following instanceof \Illuminate\Support\Collection ? $following->all() : []); @endphp
            @forelse($followingItems as $follow)
                @php $person = $follow->followee; @endphp
                @if($person)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-4 hover:shadow-md transition-shadow"
                        id="following-{{ $person->id }}">
                        <a href="{{ route('profile.show', $person->id) }}" class="flex-shrink-0">
                            <img src="{{ $person->avatar_url }}" alt="{{ $person->name }}"
                                class="w-12 h-12 rounded-full object-cover border border-gray-100 shadow-sm">
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('profile.show', $person->id) }}"
                                class="font-semibold text-gray-900 hover:text-blue-600 transition-colors truncate block">
                                {{ $person->name }}
                            </a>
                            <p class="text-sm text-gray-500 truncate">{{ '@' . $person->username }}</p>
                        </div>
                        @auth
                            @if(auth()->id() !== $person->id)
                                @php
                                    $myFollowStatus = \App\Models\Follow::where('follower_id', auth()->id())
                                        ->where('followee_id', $person->id)
                                        ->first()?->status?->value ?? 'none';
                                @endphp
                                @include('components.follow-button', ['targetUser' => $person, 'followStatus' => $myFollowStatus])
                            @endif
                        @endauth
                    </div>
                @endif
            @empty
                <div class="bg-white rounded-xl border border-gray-200 p-10 text-center">
                    <svg class="w-14 h-14 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <p class="text-gray-500 font-medium">Not following anyone yet</p>
                    <p class="text-sm text-gray-400 mt-1">Accounts followed will appear here.</p>
                </div>
            @endforelse

            @if(method_exists($following, 'hasPages') && $following->hasPages())
                <div class="pt-2">{{ $following->appends(['tab' => 'following'])->links() }}</div>
            @endif
        </div>

        {{-- ── Follow Requests Tab (own profile only) ── --}}
        @auth
            @if(auth()->id() === $user->id)
                <div id="requestsTab" class="tab-panel {{ $tab !== 'requests' ? 'hidden' : '' }} space-y-3">
                    @php $requestItems = $requests instanceof \Illuminate\Contracts\Pagination\Paginator ? $requests->items() : ($requests instanceof \Illuminate\Support\Collection ? $requests->all() : []); @endphp
                    @forelse($requestItems as $follow)
                        @php $person = $follow->follower; @endphp
                        @if($person)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-4 hover:shadow-md transition-shadow"
                                id="request-item-{{ $person->id }}">
                                <a href="{{ route('profile.show', $person->id) }}" class="flex-shrink-0">
                                    <img src="{{ $person->avatar_url }}" alt="{{ $person->name }}"
                                        class="w-12 h-12 rounded-full object-cover border border-gray-100 shadow-sm">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('profile.show', $person->id) }}"
                                        class="font-semibold text-gray-900 hover:text-blue-600 transition-colors truncate block">
                                        {{ $person->name }}
                                    </a>
                                    <p class="text-sm text-gray-500 truncate">{{ '@' . $person->username }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $follow->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex gap-2 flex-shrink-0" id="req-actions-{{ $person->id }}">
                                    <button onclick="handleFollowRequest({{ $person->id }}, 'accept')"
                                        class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-full hover:bg-blue-700 active:scale-95 transition-all">
                                        Accept
                                    </button>
                                    <button onclick="handleFollowRequest({{ $person->id }}, 'decline')"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-200 active:scale-95 transition-all">
                                        Decline
                                    </button>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="bg-white rounded-xl border border-gray-200 p-10 text-center">
                            <svg class="w-14 h-14 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-500 font-medium">No pending follow requests</p>
                            <p class="text-sm text-gray-400 mt-1">You're all caught up!</p>
                        </div>
                    @endforelse

                    @if(method_exists($requests, 'hasPages') && $requests->hasPages())
                        <div class="pt-2">{{ $requests->appends(['tab' => 'requests'])->links() }}</div>
                    @endif
                </div>
            @endif
        @endauth

    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
    <script>
        // ── Tab switching ──────────────────────────────────────────────────────────
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const tab = this.dataset.tab;

                // Update buttons
                document.querySelectorAll('.tab-btn').forEach(b => {
                    const isActive = b.dataset.tab === tab;
                    b.classList.toggle('text-blue-600', isActive);
                    b.classList.toggle('text-gray-500', !isActive);
                    const line = b.querySelector('span.absolute');
                    if (line) line.style.display = isActive ? '' : 'none';
                });

                // Show/hide panels
                document.querySelectorAll('.tab-panel').forEach(p => {
                    const isTarget = p.id === tab + 'Tab';
                    p.classList.toggle('hidden', !isTarget);
                });
            });
        });

        // ── Accept / Decline follow request ───────────────────────────────────────
        async function handleFollowRequest(userId, action) {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const url = action === 'accept'
                ? `/follow-requests/${userId}/accept`
                : `/follow-requests/${userId}/decline`;

            const res = await fetch(url, {
                method: action === 'accept' ? 'POST' : 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            const data = await res.json();

            if (data.success) {
                // Fade out and remove the request card
                const card = document.getElementById(`request-item-${userId}`);
                if (card) {
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(20px)';
                    setTimeout(() => card.remove(), 300);
                }
                showPageToast(data.message, 'success');
            } else {
                showPageToast(data.message || 'Something went wrong.', 'error');
            }
        }

        // ── Global toast ──────────────────────────────────────────────────────────
        function showPageToast(message, type = 'success') {
            let toast = document.getElementById('page-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'page-toast';
                document.body.appendChild(toast);
            }

            toast.textContent = message;
            toast.className = `fixed bottom-6 left-1/2 -translate-x-1/2 z-[300] px-5 py-3 rounded-xl shadow-xl
            text-white text-sm font-medium transition-all duration-300
            ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;

            setTimeout(() => { if (toast) toast.remove(); }, 3500);
        }
    </script>
@endpush