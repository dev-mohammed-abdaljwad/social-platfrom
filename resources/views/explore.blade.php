@extends('layouts.app')

@section('title', 'Explore - SocialHub')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Search Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Explore</h1>
            <div class="relative">
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Search posts, people, or topics..." 
                    class="w-full px-4 py-3 pl-12 bg-gray-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors"
                >
                <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Categories -->
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            <button class="category-btn px-4 py-2 bg-blue-600 text-white rounded-full text-sm font-medium whitespace-nowrap" data-category="all">
                All
            </button>
            <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-200 transition-colors" data-category="people">
                People
            </button>
            <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-200 transition-colors" data-category="posts">
                Posts
            </button>
            <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-200 transition-colors" data-category="trending">
                Trending
            </button>
        </div>

        <!-- Trending Topics -->
        <div id="trendingSection" class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Trending Topics</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div id="trendingList" class="space-y-3">
                    <a href="#" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                        <div>
                            <span class="text-xs text-gray-500">Trending</span>
                            <h3 class="font-medium text-gray-800">#WebDevelopment</h3>
                            <span class="text-sm text-gray-500">1.2K posts</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    <a href="#" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                        <div>
                            <span class="text-xs text-gray-500">Trending</span>
                            <h3 class="font-medium text-gray-800">#Laravel</h3>
                            <span class="text-sm text-gray-500">856 posts</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    <a href="#" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                        <div>
                            <span class="text-xs text-gray-500">Trending</span>
                            <h3 class="font-medium text-gray-800">#TechNews</h3>
                            <span class="text-sm text-gray-500">654 posts</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- People to Follow -->
        <div id="peopleSection" class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">People to Follow</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div id="peopleList" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Loading skeleton -->
                    <div class="animate-pulse p-4 flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Posts -->
        <div id="postsSection">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Popular Posts</h2>
            <div id="postsList" class="space-y-4">
                <!-- Loading skeleton -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 animate-pulse">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/6 mb-4"></div>
                            <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results (hidden by default) -->
        <div id="searchResults" class="hidden">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Search Results</h2>
            <div id="resultsList" class="space-y-4"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const token = localStorage.getItem('auth_token');
    let searchTimeout;

    // Category switching
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.category-btn').forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white');
                b.classList.add('bg-gray-100', 'text-gray-700');
            });
            
            this.classList.remove('bg-gray-100', 'text-gray-700');
            this.classList.add('bg-blue-600', 'text-white');
            
            filterByCategory(this.dataset.category);
        });
    });

    function filterByCategory(category) {
        document.getElementById('trendingSection').classList.toggle('hidden', category === 'people' || category === 'posts');
        document.getElementById('peopleSection').classList.toggle('hidden', category === 'posts' || category === 'trending');
        document.getElementById('postsSection').classList.toggle('hidden', category === 'people' || category === 'trending');
    }

    // Search
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('trendingSection').classList.remove('hidden');
            document.getElementById('peopleSection').classList.remove('hidden');
            document.getElementById('postsSection').classList.remove('hidden');
            return;
        }
        
        searchTimeout = setTimeout(() => search(query), 300);
    });

    async function search(query) {
        document.getElementById('trendingSection').classList.add('hidden');
        document.getElementById('peopleSection').classList.add('hidden');
        document.getElementById('postsSection').classList.add('hidden');
        document.getElementById('searchResults').classList.remove('hidden');
        
        const container = document.getElementById('resultsList');
        container.innerHTML = '<div class="text-center py-8"><div class="animate-spin w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full mx-auto"></div></div>';
        
        try {
            // Search users
            const usersResponse = await fetch(`/api/v1/users?search=${encodeURIComponent(query)}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const usersData = await usersResponse.json();
            
            // Search posts
            const postsResponse = await fetch(`/api/v1/posts?search=${encodeURIComponent(query)}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const postsData = await postsResponse.json();
            
            let results = '';
            
            // Users section
            if (usersData.data && usersData.data.length > 0) {
                results += '<h3 class="font-semibold text-gray-800 mb-3">People</h3>';
                results += '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">';
                results += usersData.data.map(user => createUserCard(user)).join('');
                results += '</div>';
            }
            
            // Posts section
            if (postsData.data && postsData.data.length > 0) {
                results += '<h3 class="font-semibold text-gray-800 mb-3">Posts</h3>';
                results += postsData.data.map(post => createPostCard(post)).join('');
            }
            
            if (!results) {
                results = `
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-gray-500">No results found for "${query}"</p>
                    </div>
                `;
            }
            
            container.innerHTML = results;
        } catch (error) {
            console.error('Search error:', error);
            container.innerHTML = '<div class="text-center py-8 text-red-500">Error loading results</div>';
        }
    }

    // Load people
    async function loadPeople() {
        const container = document.getElementById('peopleList');
        
        try {
            const response = await fetch('/api/v1/users', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.slice(0, 6).map(user => createUserCard(user)).join('');
            } else {
                container.innerHTML = '<div class="col-span-full text-center py-4 text-gray-500">No users found</div>';
            }
        } catch (error) {
            console.error('Error loading people:', error);
        }
    }

    // Load posts
    async function loadPosts() {
        const container = document.getElementById('postsList');
        
        try {
            const response = await fetch('/api/v1/posts', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.slice(0, 10).map(post => createPostCard(post)).join('');
            } else {
                container.innerHTML = `
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                        <p class="text-gray-500">No posts found</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading posts:', error);
        }
    }

    function createUserCard(user) {
        const userImage = user.profile_picture 
            ? `/storage/${user.profile_picture}` 
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=60`;
        
        return `
            <a href="/profile/${user.id}" class="flex items-center gap-3 p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors">
                <img src="${userImage}" alt="${user.name}" class="w-12 h-12 rounded-full object-cover">
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-800 truncate">${user.name}</h3>
                    <p class="text-sm text-gray-500 truncate">@${user.username || 'user'}</p>
                </div>
                <button onclick="event.preventDefault(); sendFriendRequest(${user.id})" class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Follow
                </button>
            </a>
        `;
    }

    function createPostCard(post) {
        const userImage = post.user?.profile_picture 
            ? `/storage/${post.user.profile_picture}` 
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user?.name || 'User')}&size=50`;
        
        return `
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex gap-4">
                    <a href="/profile/${post.user?.id}">
                        <img src="${userImage}" alt="${post.user?.name}" class="w-10 h-10 rounded-full object-cover">
                    </a>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <a href="/profile/${post.user?.id}" class="font-semibold text-gray-800 hover:underline">${post.user?.name || 'User'}</a>
                            <span class="text-sm text-gray-500">@${post.user?.username || 'user'}</span>
                        </div>
                        <p class="text-sm text-gray-500 mb-2">${formatDate(post.created_at)}</p>
                        <p class="text-gray-700">${post.content}</p>
                        ${post.media_url ? `<img src="${post.media_url}" alt="Post" class="mt-3 rounded-lg max-h-60 w-full object-cover">` : ''}
                        <div class="flex items-center gap-6 mt-3 pt-3 border-t border-gray-100">
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

    async function sendFriendRequest(userId) {
        try {
            const response = await fetch(`/api/v1/friends/send/${userId}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                alert('Friend request sent!');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadPeople();
        loadPosts();
    });
</script>
@endpush
