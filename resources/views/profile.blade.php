@extends('layouts.app')

@section('title', ($user->name ?? 'Profile') . ' - SocialTime')

@section('content')
    <!-- Profile Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <!-- Cover Photo -->
        <div
            class="h-48 relative overflow-hidden {{ $user->cover_photo ? '' : 'bg-gradient-to-r from-blue-500 to-purple-600' }}">
            @if($user->cover_photo)
                <img src="{{ $user->cover_url }}" alt="Cover photo" class="w-full h-full object-cover" id="coverPhotoImage">
            @else
                <div class="w-full h-full bg-gradient-to-r from-blue-500 to-purple-600" id="coverPhotoPlaceholder"></div>
            @endif

            @if(isset($isOwnProfile) && $isOwnProfile)
                <div class="absolute bottom-4 right-4 flex gap-2">
                    @if($user->cover_photo)
                        <button onclick="removeCoverPhoto()"
                            class="px-3 py-2 bg-red-500/80 backdrop-blur-sm text-white rounded-lg hover:bg-red-600/80 transition-colors text-sm">
                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    @endif
                    <button onclick="document.getElementById('coverPhotoInput').click()"
                        class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-lg hover:bg-white/30 transition-colors text-sm">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $user->cover_photo ? 'Change Cover' : 'Add Cover' }}
                    </button>
                    <input type="file" id="coverPhotoInput" class="hidden"
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" onchange="uploadCoverPhoto(this)">
                </div>
            @endif
        </div>

        <!-- Profile Info -->
        <div class="px-6 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end -mt-16 sm:-mt-12 gap-4">
                <!-- Profile Picture -->
                <div class="relative group">
                    <img id="profilePictureImage" src="{{ $user->avatar_url }}" alt="{{ $user->name ?? 'User' }}"
                        class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover bg-white">
                    @if(isset($isOwnProfile) && $isOwnProfile)
                        <div class="absolute inset-0 flex items-center justify-center">
                            <button onclick="document.getElementById('profilePictureInput').click()"
                                class="absolute bottom-2 right-2 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors shadow">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                        </div>
                        <input type="file" id="profilePictureInput" class="hidden"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" onchange="uploadProfilePicture(this)">
                    @endif
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
                                <a href="{{ url('/settings') }}"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                    Edit Profile
                                </a>
                            @else
                                {{-- Friend Button with different states --}}
                                <div id="user-actions-{{ $user->id }}" data-initial-status="{{ $friendshipStatus ?? 'none' }}"
                                    data-friend-id="{{ $user->id }}" data-friendship-id="{{ $friendship->id ?? '' }}"
                                    data-friend-name="{{ $user->name }}" data-friend-avatar="{{ $user->avatar_url }}">
                                </div>

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
                    <span class="block text-xl font-bold text-gray-800">{{ $posts->count() }}</span>
                    <span class="text-sm text-gray-500">Posts</span>
                </div>
                <div class="text-center">
                    <span class="block text-xl font-bold text-gray-800">{{ $sharedPosts->count() }}</span>
                    <span class="text-sm text-gray-500">Shares</span>
                </div>
                <div class="text-center">
                    <span class="block text-xl font-bold text-gray-800">{{ $friends->count() }}</span>
                    <span class="text-sm text-gray-500">Friends</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="flex border-b border-gray-200">
            <button class="flex-1 px-4 py-3 text-center font-medium text-blue-600 border-b-2 border-blue-600 tab-btn active"
                data-tab="posts">
                Posts
            </button>
            <button class="flex-1 px-4 py-3 text-center font-medium text-gray-500 hover:text-gray-700 tab-btn"
                data-tab="shares">
                Shares
            </button>
            <button class="flex-1 px-4 py-3 text-center font-medium text-gray-500 hover:text-gray-700 tab-btn"
                data-tab="friends">
                Friends
            </button>
            <button class="flex-1 px-4 py-3 text-center font-medium text-gray-500 hover:text-gray-700 tab-btn"
                data-tab="about">
                About
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="postsTab" class="tab-content space-y-6">
        @forelse($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <p class="text-gray-500">No posts yet.</p>
            </div>
        @endforelse
    </div>



    <div id="sharesTab" class="tab-content hidden space-y-6">
        @forelse($sharedPosts as $share)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4" data-share-id="{{ $share->id }}">
                <!-- Share Header -->
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z">
                            </path>
                        </svg>
                        <span class="text-sm text-gray-500">{{ $user->name }} shared this post</span>
                        <span class="text-sm text-gray-400">{{ $share->created_at->diffForHumans() }}</span>
                    </div>
                    @if(auth()->check() && auth()->id() === $share->user_id)
                        <div class="relative">
                            <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors share-menu-btn"
                                data-share-id="{{ $share->id }}">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                    </path>
                                </svg>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 hidden z-10 share-menu"
                                data-share-id="{{ $share->id }}">
                                <button
                                    class="w-full px-4 py-2 text-left text-gray-700 hover:bg-gray-100 rounded-t-lg edit-share-btn"
                                    data-share-id="{{ $share->id }}" data-content="{{ $share->content }}">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Edit Share
                                    </span>
                                </button>
                                <button
                                    class="w-full px-4 py-2 text-left text-red-600 hover:bg-red-50 rounded-b-lg delete-share-btn"
                                    data-share-id="{{ $share->id }}">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                        Delete Share
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                @if($share->content)
                    <p class="text-gray-700 mb-3 share-content">{{ $share->content }}</p>
                @endif

                <!-- Original Post -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex gap-4">
                        <a href="{{ route('profile.show', $share->post->user) }}">
                            <img src="{{ $share->post->user->avatar_url }}" alt="{{ $share->post->user->name }}"
                                class="w-10 h-10 rounded-full object-cover">
                        </a>
                        <div class="flex-1">
                            <div>
                                <a href="{{ route('profile.show', $share->post->user) }}"
                                    class="font-semibold text-gray-800 hover:text-blue-600">{{ $share->post->user->name }}</a>
                                <p class="text-sm text-gray-500">{{ $share->post->created_at->diffForHumans() }}</p>
                            </div>

                            @if($share->post->content)
                                <p class="mt-3 text-gray-700 whitespace-pre-line">{{ $share->post->content }}</p>
                            @endif

                            @if($share->post->image_url)
                                <img src="{{ $share->post->image_url }}" alt="Post image"
                                    class="mt-3 rounded-lg max-h-96 w-full object-cover">
                            @endif

                            @if($share->post->video_url)
                                <video src="{{ $share->post->video_url }}" controls class="mt-3 rounded-lg max-h-96 w-full"></video>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z">
                    </path>
                </svg>
                <p class="text-gray-500">No shared posts yet.</p>
            </div>
        @endforelse
    </div>

    <div id="friendsTab" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Friends ({{ $friends->count() }})</h3>
            @if($friends->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($friends as $friend)
                        <a href="{{ route('profile.show', $friend) }}"
                            class="block p-3 hover:bg-gray-50 rounded-lg transition-colors text-center">
                            <img src="{{ $friend->avatar_url }}" alt="{{ $friend->name }}"
                                class="w-20 h-20 rounded-full mx-auto mb-2 object-cover">
                            <p class="font-medium text-gray-800 truncate">{{ $friend->name }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ '@' . $friend->username }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <p class="text-gray-500">No friends yet.</p>
                </div>
            @endif
        </div>
    </div>

    <div id="aboutTab" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">About</h3>
            <div class="space-y-4">
                @if($user->bio)
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-gray-700">{{ $user->bio }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span class="text-gray-700">{{ $user->email }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span class="text-gray-700">Joined {{ $user->created_at->format('F Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="shareModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Share Post</h3>
                <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <textarea id="shareContent" placeholder="Add a comment to your share (optional)..."
                    class="w-full px-4 py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    rows="3"></textarea>

                <!-- Post Preview -->
                <div id="sharePostPreview" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <!-- Will be filled dynamically -->
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
                <button onclick="closeShareModal()"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmShare()"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                    Share
                </button>
            </div>
        </div>
    </div>

    <!-- Likes Modal -->
    <div id="likesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Likes</h3>
                <button onclick="closeLikesModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-4 overflow-y-auto flex-1" id="likesModalContent">
                <div class="text-center py-4 text-gray-500">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div id="editPostModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Edit Post</h3>
                <button onclick="closeEditPostModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <input type="hidden" id="editPostId">
                <textarea id="editPostContent" placeholder="What's on your mind?"
                    class="w-full px-4 py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    rows="4"></textarea>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Privacy</label>
                    <select id="editPostPrivacy"
                        class="w-full px-4 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="public">Public</option>
                        <option value="friends">Friends Only</option>
                        <option value="private">Only Me</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
                <button onclick="closeEditPostModal()"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="saveEditPost()"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Share Modal -->
    <div id="editShareModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Edit Share</h3>
                <button onclick="closeEditShareModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <input type="hidden" id="editShareId">
                <textarea id="editShareContent" placeholder="Add a comment to your share (optional)..."
                    class="w-full px-4 py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    rows="3"></textarea>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
                <button onclick="closeEditShareModal()"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="saveEditShare()"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 overflow-hidden">
            <div class="p-6 text-center">
                <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800 mb-2" id="deleteConfirmTitle">Delete Post?</h3>
                <p class="text-gray-600 mb-6" id="deleteConfirmMessage">This action cannot be undone. Are you sure you want
                    to delete this post?</p>
                <input type="hidden" id="deleteItemId">
                <input type="hidden" id="deleteItemType">
                <div class="flex justify-center gap-3">
                    <button onclick="closeDeleteConfirmModal()"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium">
                        Cancel
                    </button>
                    <button onclick="confirmDelete()"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                        Delete
                    </button>
                </div>
            </div>
        </div>

    </div>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    </script>
@endsection
@push('scripts')
    <script src="{{ asset('js/modules/posts.js') }}"></script>
    <script src="{{ asset('js/modules/reactions.js') }}"></script>
    <script src="{{ asset('js/modules/comments.js') }}"></script>
    <script src="{{ asset('js/modules/media.js') }}"></script>
    <script src="{{ asset('js/modules/profile.js') }}"></script>
    <script src="{{ asset('js/modules/settings.js') }}"></script>
@endpush