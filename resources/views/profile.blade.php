@extends('layouts.app')

@section('title', ($user->name ?? 'Profile') . ' - SocialHub')

@section('content')
    <!-- Profile Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <!-- Cover Photo -->
        <div class="h-48 bg-gradient-to-r from-blue-500 to-purple-600 relative">
            <button class="absolute bottom-4 right-4 px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-lg hover:bg-white/30 transition-colors text-sm">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Edit Cover
            </button>
        </div>

        <!-- Profile Info -->
        <div class="px-6 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end -mt-16 sm:-mt-12 gap-4">
                <!-- Profile Picture -->
                <div class="relative">
                    <img 
                        src="{{ isset($user) && $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'User') . '&size=150' }}" 
                        alt="{{ $user->name ?? 'User' }}" 
                        class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover bg-white"
                    >
                    <button class="absolute bottom-2 right-2 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors shadow">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Name and Stats -->
                <div class="flex-1 sm:ml-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">{{ $user->name ?? 'User Name' }}</h1>
                            <p class="text-gray-500">{{ '@' . ($user->username ?? 'username') }}</p>
                        </div>
                        <div class="flex gap-2">
                            @if(isset($isOwnProfile) && $isOwnProfile)
                            <a href="{{ url('/settings') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                Edit Profile
                            </a>
                            @else
                            <button id="friendBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Add Friend
                            </button>
                            <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                Message
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bio -->
            <p class="mt-4 text-gray-700">{{ $user->bio ?? 'No bio yet.' }}</p>

            <!-- Stats -->
            <div class="flex gap-6 mt-4 pt-4 border-t border-gray-100">
                <div class="text-center">
                    <span class="block text-xl font-bold text-gray-800" id="postsCount">0</span>
                    <span class="text-sm text-gray-500">Posts</span>
                </div>
                <div class="text-center">
                    <span class="block text-xl font-bold text-gray-800" id="friendsCount">0</span>
                    <span class="text-sm text-gray-500">Friends</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="flex border-b border-gray-200">
            <button class="flex-1 px-4 py-3 text-center font-medium text-blue-600 border-b-2 border-blue-600 tab-btn active" data-tab="posts">
                Posts
            </button>
            <button class="flex-1 px-4 py-3 text-center font-medium text-gray-500 hover:text-gray-700 tab-btn" data-tab="about">
                About
            </button>
            <button class="flex-1 px-4 py-3 text-center font-medium text-gray-500 hover:text-gray-700 tab-btn" data-tab="friends">
                Friends
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="postsTab" class="tab-content space-y-6">
        <!-- User's posts will be loaded here -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center animate-pulse">
            <div class="w-16 h-16 bg-gray-200 rounded-full mx-auto mb-4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto"></div>
        </div>
    </div>

    <div id="aboutTab" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">About</h3>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-gray-700">{{ $user->email ?? 'email@example.com' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-gray-700">Joined {{ isset($user->created_at) ? $user->created_at->format('F Y') : 'Recently' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div id="friendsTab" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Friends</h3>
            <div id="friendsList" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <!-- Friends will be loaded here -->
                <div class="animate-pulse">
                    <div class="w-full aspect-square bg-gray-200 rounded-lg mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
                b.classList.add('text-gray-500');
            });
            
            this.classList.remove('text-gray-500');
            this.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
            
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            
            const tabId = this.dataset.tab + 'Tab';
            document.getElementById(tabId).classList.remove('hidden');
        });
    });

    // Load user posts
    async function loadUserPosts(userId) {
        const container = document.getElementById('postsTab');
        const token = localStorage.getItem('auth_token');
        
        try {
            const response = await fetch(`/api/v1/users/${userId}/posts`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.data && data.data.length > 0) {
                document.getElementById('postsCount').textContent = data.data.length;
                container.innerHTML = data.data.map(post => createPostHTML(post)).join('');
            } else {
                container.innerHTML = `
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <p class="text-gray-500">No posts yet.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading posts:', error);
        }
    }

    // Load friends
    async function loadFriends() {
        const container = document.getElementById('friendsList');
        const token = localStorage.getItem('auth_token');
        
        try {
            const response = await fetch('/api/v1/friends', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.data && data.data.length > 0) {
                document.getElementById('friendsCount').textContent = data.data.length;
                container.innerHTML = data.data.map(friend => `
                    <a href="/profile/${friend.id}" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors text-center">
                        <img 
                            src="${friend.profile_picture ? '/storage/' + friend.profile_picture : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(friend.name)}" 
                            alt="${friend.name}" 
                            class="w-20 h-20 rounded-full mx-auto mb-2 object-cover"
                        >
                        <p class="font-medium text-gray-800 truncate">${friend.name}</p>
                        <p class="text-sm text-gray-500 truncate">@${friend.username}</p>
                    </a>
                `).join('');
            } else {
                container.innerHTML = `<div class="col-span-full text-center py-8"><p class="text-gray-500">No friends yet.</p></div>`;
            }
        } catch (error) {
            console.error('Error loading friends:', error);
        }
    }

    function createPostHTML(post) {
        const userImage = post.user?.profile_picture 
            ? `/storage/${post.user.profile_picture}` 
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user?.name || 'User')}`;
        
        return `
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex gap-4">
                    <img src="${userImage}" alt="${post.user?.name}" class="w-10 h-10 rounded-full object-cover">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">${post.user?.name || 'User'}</h4>
                        <p class="text-sm text-gray-500">${formatDate(post.created_at)}</p>
                        <p class="mt-3 text-gray-700">${post.content}</p>
                        ${post.media_url ? `<img src="${post.media_url}" alt="Post" class="mt-3 rounded-lg max-h-96 w-full object-cover">` : ''}
                        <div class="flex items-center gap-6 mt-4 pt-4 border-t border-gray-100">
                            <button class="flex items-center gap-2 text-gray-600 hover:text-red-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span>${post.likes_count || 0}</span>
                            </button>
                            <button class="flex items-center gap-2 text-gray-600 hover:text-blue-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span>${post.comments_count || 0}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 7) return `${days}d ago`;
        return date.toLocaleDateString();
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        const userId = {{ $user->id ?? 'null' }};
        if (userId) {
            loadUserPosts(userId);
            loadFriends();
        }
    });
</script>
@endpush