@extends('layouts.app')

@section('title', 'Home - SocialHub')

@section('content')
    <!-- Create Post Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex gap-4">
            <img 
                src="{{ auth()->check() ? (auth()->user()->profile_picture ? asset('storage/' . auth()->user()->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name)) : 'https://ui-avatars.com/api/?name=Guest' }}" 
                alt="Profile" 
                class="w-10 h-10 rounded-full object-cover"
            >
            <div class="flex-1">
                <textarea 
                    placeholder="What's on your mind?" 
                    class="w-full px-4 py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    rows="3"
                    id="postContent"
                ></textarea>
                <div class="flex items-center justify-between mt-3">
                    <div class="flex gap-2">
                        <button class="flex items-center gap-2 px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm">Photo</span>
                        </button>
                        <button class="flex items-center gap-2 px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm">Video</span>
                        </button>
                    </div>
                    <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium" id="postButton">
                        Post
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts Feed -->
    <div id="postsFeed" class="space-y-6">
        <!-- Loading skeleton -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 animate-pulse">
            <div class="flex gap-4">
                <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                <div class="flex-1">
                    <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/6"></div>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <div class="h-4 bg-gray-200 rounded"></div>
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            </div>
        </div>
    </div>
@endsection

@section('rightSidebar')
    <!-- Friend Suggestions -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Friend Suggestions</h3>
        <div class="space-y-3" id="friendSuggestions">
            <!-- Loading -->
            <div class="flex items-center gap-3 animate-pulse">
                <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                <div class="flex-1">
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-1"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trending Topics -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Trending</h3>
        <div class="space-y-3">
            <a href="#" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <p class="text-sm text-gray-500">#trending</p>
                <p class="font-medium text-gray-800">Technology</p>
                <p class="text-sm text-gray-500">2.5k posts</p>
            </a>
            <a href="#" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <p class="text-sm text-gray-500">#trending</p>
                <p class="font-medium text-gray-800">Sports</p>
                <p class="text-sm text-gray-500">1.8k posts</p>
            </a>
            <a href="#" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <p class="text-sm text-gray-500">#trending</p>
                <p class="font-medium text-gray-800">Music</p>
                <p class="text-sm text-gray-500">1.2k posts</p>
            </a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Post component template
    function createPostHTML(post) {
        const userImage = post.user.profile_picture 
            ? `/storage/${post.user.profile_picture}` 
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.name)}`;
        
        return `
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4" data-post-id="${post.id}">
                <div class="flex gap-4">
                    <img src="${userImage}" alt="${post.user.name}" class="w-10 h-10 rounded-full object-cover">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-800">${post.user.name}</h4>
                                <p class="text-sm text-gray-500">${formatDate(post.created_at)}</p>
                            </div>
                            <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-3 text-gray-700">${post.content}</p>
                        ${post.media_url ? `<img src="${post.media_url}" alt="Post image" class="mt-3 rounded-lg max-h-96 w-full object-cover">` : ''}
                        <div class="flex items-center gap-6 mt-4 pt-4 border-t border-gray-100">
                            <button class="flex items-center gap-2 text-gray-600 hover:text-red-500 transition-colors like-btn" data-post-id="${post.id}">
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
                            <button class="flex items-center gap-2 text-gray-600 hover:text-green-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                <span>Share</span>
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

    // Load posts on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadPosts();
    });

    async function loadPosts() {
        const feed = document.getElementById('postsFeed');
        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch('/api/v1/posts/feed', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.data && data.data.length > 0) {
                feed.innerHTML = data.data.map(post => createPostHTML(post)).join('');
            } else {
                feed.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <p class="text-gray-500">No posts yet. Be the first to share something!</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading posts:', error);
            feed.innerHTML = `
                <div class="text-center py-12">
                    <p class="text-gray-500">Unable to load posts. Please try again later.</p>
                </div>
            `;
        }
    }

    // Create new post
    document.getElementById('postButton').addEventListener('click', async function() {
        const content = document.getElementById('postContent').value.trim();
        if (!content) return;

        const token = localStorage.getItem('auth_token');
        try {
            const response = await fetch('/api/v1/posts', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content })
            });

            if (response.ok) {
                document.getElementById('postContent').value = '';
                loadPosts();
            }
        } catch (error) {
            console.error('Error creating post:', error);
        }
    });
</script>
@endpush
