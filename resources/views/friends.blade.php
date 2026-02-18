@extends('layouts.app')

@section('title', 'Friends - SocialTime')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Friends</h1>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="flex border-b border-gray-200"> 
                <button class="flex-1 px-4 py-3 text-center font-medium text-blue-600 border-b-2 border-blue-600 tab-btn active" data-tab="friends">
                    My Friends ({{ $friends->count() }})
                </button>
                <button class="flex-1 px-4 py-3 text-center font-medium text-gray-500 hover:text-gray-700 tab-btn" data-tab="requests">
                    Friend Requests
                    @if($pendingRequests->count() > 0)
                    <span class="ml-1 px-2 py-0.5 bg-red-500 text-white text-xs rounded-full">{{ $pendingRequests->count() }}</span>
                    @endif
                </button>
                <button class="flex-1 px-4 py-3 text-center font-medium text-gray-500 hover:text-gray-700 tab-btn" data-tab="sent">
                    Sent Requests ({{ $sentRequests->count() }})
                </button>
            </div>
        </div>

        <!-- Friends Tab -->
        <div id="friendsTab" class="tab-content">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                @if($friends->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($friends as $friend)
                    <div class="p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors" id="friend-{{ $friend->id }}">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile.show', $friend) }}">
                                <img src="{{ $friend->avatar_url }}" alt="{{ $friend->name }}" class="w-14 h-14 rounded-full object-cover">
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('profile.show', $friend) }}" class="font-semibold text-gray-800 truncate block hover:text-blue-600">{{ $friend->name }}</a>
                                <p class="text-sm text-gray-500 truncate">{{ '@' . $friend->username }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <a href="{{ route('profile.show', $friend) }}" class="flex-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium text-center hover:bg-gray-200 transition-colors">View Profile</a>
                            <button onclick="removeFriend({{ $friend->id }})" class="px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors">Remove</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="text-gray-500">No friends yet. Start connecting with people!</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Requests Tab -->
        <div id="requestsTab" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                @if($pendingRequests->count() > 0)
                <div class="space-y-4">
                    @foreach($pendingRequests as $request)
                    <div class="p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-between" id="request-{{ $request->id }}">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile.show', $request->sender) }}">
                                <img src="{{ $request->sender->avatar_url }}" alt="{{ $request->sender->name }}" class="w-14 h-14 rounded-full object-cover">
                            </a>
                            <div>
                                <a href="{{ route('profile.show', $request->sender) }}" class="font-semibold text-gray-800 hover:text-blue-600">{{ $request->sender->name }}</a>
                                <p class="text-sm text-gray-500">{{ '@' . $request->sender->username }}</p>
                                <p class="text-xs text-gray-400">{{ $request->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="acceptFriendRequest({{ $request->id }},{{ auth()->user()->id }})" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">Accept</button>
                            <button onclick="rejectFriendRequest({{ $request->id }},{{ auth()->user()->id }})" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition-colors">Decline</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    <p class="text-gray-500">No pending friend requests.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sent Requests Tab -->
        <div id="sentTab" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                @if($sentRequests->count() > 0)
                <div class="space-y-4">
                    @foreach($sentRequests as $request)
                    <div class="p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-between" id="sent-{{ $request->id }}">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile.show', $request->receiver) }}">
                                <img src="{{ $request->receiver->avatar_url }}" alt="{{ $request->receiver->name }}" class="w-14 h-14 rounded-full object-cover">
                            </a>
                            <div>
                                <a href="{{ route('profile.show', $request->receiver) }}" class="font-semibold text-gray-800 hover:text-blue-600">{{ $request->receiver->name }}</a>
                                <p class="text-sm text-gray-500">{{ '@' . $request->receiver->username }}</p>
                                <p class="text-xs text-gray-400">Sent {{ $request->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <button onclick="cancelFriendRequest({{ $request->id }},{{ auth()->user()->id }})" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">Cancel</button>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <p class="text-gray-500">No sent requests.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Suggestions -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">People You May Know</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                @if($suggestions->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($suggestions as $user)
                    <div class="p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors" id="suggestion-{{ $user->id }}">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile.show', $user) }}">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-full object-cover">
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('profile.show', $user) }}" class="font-semibold text-gray-800 truncate block hover:text-blue-600">{{ $user->name }}</a>
                                <p class="text-sm text-gray-500 truncate">{{ '@' . $user->username }}</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button onclick="sendFriendRequest({{ $user->id }})" class="w-full px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">Add Friend</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <p class="text-gray-500">No suggestions available.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    <meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>
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
</script>
<script src="{{ asset('js/modules/search.js') }}"></script>
@endpush
