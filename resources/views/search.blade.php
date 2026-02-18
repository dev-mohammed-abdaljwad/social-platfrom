@extends('layouts.app')

@section('title', 'Search - SocialTime')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Search Users</h1>

        <!-- Search Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form action="{{ route('search') }}" method="GET" class="flex gap-4">
                <div class="flex-1 relative">
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $query }}"
                        placeholder="Search by name or username..." 
                        class="w-full px-4 py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors"
                        autofocus
                    >
                    <svg class="absolute right-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    Search
                </button>
            </form>
        </div>

        <!-- Search Results -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            @if(strlen($query) < 2 && strlen($query) > 0)
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <p class="text-gray-500">Please enter at least 2 characters to search.</p>
                </div>
            @elseif(strlen($query) >= 2)
                @if($users->count() > 0)
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        Found {{ $users->total() }} {{ Str::plural('user', $users->total()) }} for "{{ $query }}"
                    </h2>
                    <div class="space-y-4">
                        @foreach($users as $user)
                        <div class="p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-between" id="user-{{ $user->id }}">
                            <div class="flex items-center gap-4">
                                <a href="{{ route('profile.show', $user) }}">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-full object-cover">
                                </a>
                                <div>
                                    <a href="{{ route('profile.show', $user) }}" class="font-semibold text-gray-800 hover:text-blue-600">{{ $user->name }}</a>
                                    <p class="text-sm text-gray-500">{{ '@' . $user->username }}</p>
                                    @if($user->bio)
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-1">{{ $user->bio }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            @auth
                            <div class="flex gap-2" id="user-actions-{{ $user->id }}">
                                @php
                                    $status = $user->friendship_status ?? ['status' => 'none'];
                                @endphp
                                
                                @if($status['status'] === 'accepted')
                                    <a href="{{ route('profile.show', $user) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                                        View Profile
                                    </a>
                                    <button onclick="removeFriend({{ $user->id }})" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors">
                                        Remove
                                    </button>
                                @elseif($status['status'] === 'pending')
                                   
                                        <button onclick="acceptRequest({{ $status['friendship']->id }}, {{ $user->id }})" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                            Accept
                                        </button>
                                        <button onclick="rejectRequest({{ $status['friendship']->id }}, {{ $user->id }})" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition-colors">
                                            Decline
                                        </button>
                                    @endif
                                @else
                                    <button onclick="sendRequest({{ $user->id }})" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                        Add Friend
                                    </button>
                                @endif
                            </div>
                            @else
                            <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                Login to Connect
                            </a>
                            @endauth
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                    <div class="mt-6">
                        {{ $users->withQueryString()->links() }}
                    </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-gray-500">No users found for "{{ $query }}"</p>
                        <p class="text-sm text-gray-400 mt-2">Try searching with a different name or username.</p>
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <p class="text-gray-500">Start typing to search for users.</p>
                    <p class="text-sm text-gray-400 mt-2">Find friends by their name or username.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/modules/search.js') }}"></script>
    
@endpush
