@extends('layouts.app')

@section('title', 'Friends - SocialHub')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Friends</h1>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="flex border-b border-gray-200"> 
                <button class="flex-1 px-4 py-3 text-center font-medium text-blue-600 border-b-2 border-blue-600 tab-btn active" data-tab="friends">
                    My Friends
                </button>
                <button class="flex-1 px-4 py-3 text-center font-medium text-gray-500 hover:text-gray-700 tab-btn" data-tab="requests">
                    Friend Requests
                    <span id="requestsCount" class="ml-1 px-2 py-0.5 bg-red-500 text-white text-xs rounded-full hidden">0</span>
                </button>
                <button class="flex-1 px-4 py-3 text-center font-medium text-gray-500 hover:text-gray-700 tab-btn" data-tab="sent">
                    Sent Requests
                </button>
            </div>
        </div>

        <!-- Friends Tab -->
        <div id="friendsTab" class="tab-content">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div id="friendsList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Loading skeleton -->
                    <div class="animate-pulse p-4 flex items-center gap-3">
                        <div class="w-14 h-14 bg-gray-200 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Tab -->
        <div id="requestsTab" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div id="requestsList" class="space-y-4">
                    <!-- Loading skeleton -->
                    <div class="animate-pulse p-4 flex items-center gap-3">
                        <div class="w-14 h-14 bg-gray-200 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sent Requests Tab -->
        <div id="sentTab" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div id="sentList" class="space-y-4">
                    <!-- Loading skeleton -->
                    <div class="animate-pulse p-4 flex items-center gap-3">
                        <div class="w-14 h-14 bg-gray-200 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suggestions -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">People You May Know</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div id="suggestionsList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Loading skeleton -->
                    <div class="animate-pulse p-4 flex items-center gap-3">
                        <div class="w-14 h-14 bg-gray-200 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
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

    const token = localStorage.getItem('auth_token');

    // Load friends
    async function loadFriends() {
        const container = document.getElementById('friendsList');
        
        try {
            const response = await fetch('/api/v1/friends', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.map(friend => createFriendCard(friend, 'friend')).join('');
            } else {
                container.innerHTML = `
                    <div class="col-span-full text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-gray-500">No friends yet. Start connecting!</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading friends:', error);
        }
    }

    // Load pending requests
    async function loadRequests() {
        const container = document.getElementById('requestsList');
        
        try {
            const response = await fetch('/api/v1/friends/requests', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.data && data.data.length > 0) {
                document.getElementById('requestsCount').textContent = data.data.length;
                document.getElementById('requestsCount').classList.remove('hidden');
                container.innerHTML = data.data.map(request => createFriendCard(request, 'request')).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500">No pending friend requests.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading requests:', error);
        }
    }

    // Load sent requests
    async function loadSentRequests() {
        const container = document.getElementById('sentList');
        
        try {
            const response = await fetch('/api/v1/friends/sent', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.map(request => createFriendCard(request, 'sent')).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500">No sent requests.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading sent requests:', error);
        }
    }

    // Load suggestions
    async function loadSuggestions() {
        const container = document.getElementById('suggestionsList');
        
        try {
            const response = await fetch('/api/v1/users', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.slice(0, 6).map(user => createFriendCard(user, 'suggestion')).join('');
            } else {
                container.innerHTML = `
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">No suggestions available.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading suggestions:', error);
        }
    }

    function createFriendCard(user, type) {
        const userImage = user.profile_picture 
            ? `/storage/${user.profile_picture}` 
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=60`;
        
        let actions = '';
        
        switch(type) {
            case 'friend':
                actions = `
                    <div class="flex gap-2 mt-3">
                        <a href="/profile/${user.id}" class="flex-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium text-center hover:bg-gray-200 transition-colors">View</a>
                        <button onclick="removeFriend(${user.id})" class="px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors">Remove</button>
                    </div>
                `;
                break;
            case 'request':
                actions = `
                    <div class="flex gap-2 mt-3">
                        <button onclick="acceptRequest(${user.id})" class="flex-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">Accept</button>
                        <button onclick="rejectRequest(${user.id})" class="px-3 py-1.5 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition-colors">Decline</button>
                    </div>
                `;
                break;
            case 'sent':
                actions = `
                    <div class="mt-3">
                        <button onclick="cancelRequest(${user.id})" class="w-full px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">Cancel Request</button>
                    </div>
                `;
                break;
            case 'suggestion':
                actions = `
                    <div class="mt-3">
                        <button onclick="sendRequest(${user.id})" class="w-full px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">Add Friend</button>
                    </div>
                `;
                break;
        }
        
        return `
            <div class="p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <img src="${userImage}" alt="${user.name}" class="w-14 h-14 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-800 truncate">${user.name}</h3>
                        <p class="text-sm text-gray-500 truncate">@${user.username || 'user'}</p>
                    </div>
                </div>
                ${actions}
            </div>
        `;
    }

    // Friend actions
    async function sendRequest(userId) {
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
                loadSuggestions();
                loadSentRequests();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function acceptRequest(userId) {
        try {
            const response = await fetch(`/api/v1/friends/accept/${userId}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                loadRequests();
                loadFriends();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function rejectRequest(userId) {
        try {
            const response = await fetch(`/api/v1/friends/reject/${userId}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                loadRequests();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function cancelRequest(userId) {
        try {
            const response = await fetch(`/api/v1/friends/cancel/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                loadSentRequests();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function removeFriend(userId) {
        if (!confirm('Are you sure you want to remove this friend?')) return;
        
        try {
            const response = await fetch(`/api/v1/friends/remove/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                loadFriends();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadFriends();
        loadRequests();
        loadSentRequests();
        loadSuggestions();
    });
</script>
@endpush
