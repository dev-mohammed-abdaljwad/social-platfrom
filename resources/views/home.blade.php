@extends('layouts.app')

@section('title', 'Home - SocialTime')

@section('content')
    <!-- Create Post Card -->
    @auth
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 sm:p-4 mb-4 sm:mb-6">
            <form action="{{ route('posts.store') }}" method="POST" id="createPostForm" enctype="multipart/form-data">
                @csrf
                <div class="flex gap-2 sm:gap-4">
                    <img src="{{ auth()->user()->avatar_url }}" alt="Profile"
                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <textarea name="content" placeholder="What's on your mind?"
                            class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            rows="2" id="postContent"></textarea>

                        <!-- Media Preview -->
                        <div id="mediaPreview" class="hidden mt-3 space-y-2">
                            <!-- Image Preview -->
                            <div id="imagePreviewContainer" class="hidden relative">
                                <img id="imagePreview" class="max-h-64 rounded-lg object-cover" alt="Preview">
                                <button type="button" onclick="removeImage()"
                                    class="absolute top-2 right-2 bg-gray-800 bg-opacity-70 text-white rounded-full p-1 hover:bg-opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <!-- Video Preview -->
                            <div id="videoPreviewContainer" class="hidden relative">
                                <video id="videoPreview" class="max-h-64 rounded-lg w-full" controls></video>
                                <button type="button" onclick="removeVideo()"
                                    class="absolute top-2 right-2 bg-gray-800 bg-opacity-70 text-white rounded-full p-1 hover:bg-opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Hidden File Inputs -->
                        <input type="file" id="imageInput" name="image"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="hidden"
                            onchange="previewImage(this)">
                        <input type="file" id="videoInput" name="video" accept="video/mp4,video/mpeg,video/quicktime,video/webm"
                            class="hidden" onchange="previewVideo(this)">

                        <!-- Location and Privacy -->
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-2 sm:mt-3">
                            <div class="flex-1">
                                <div class="relative">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 absolute left-2.5 sm:left-3 top-1/2 transform -translate-y-1/2"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <input type="text" name="location" placeholder="Location"
                                        class="w-full pl-8 sm:pl-10 pr-3 py-1.5 sm:py-2 bg-gray-100 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="w-full sm:w-auto sm:min-w-[140px]">
                                <div class="relative">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 absolute left-2.5 sm:left-3 top-1/2 transform -translate-y-1/2"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                    </svg>
                                    <select name="privacy"
                                        class="w-full pl-8 sm:pl-10 pr-6 sm:pr-8 py-1.5 sm:py-2 bg-gray-100 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer">
                                        <option value="public">🌍 Public</option>
                                        <option value="friends">👥 Friends</option>
                                        <option value="private">🔒 Only Me</option>
                                    </select>
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400 absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-2 sm:mt-3">
                            <div class="flex gap-1 sm:gap-2">
                                <button type="button" onclick="document.getElementById('imageInput').click()"
                                    class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span class="text-xs sm:text-sm hidden xs:inline">Photo</span>
                                </button>
                                <button type="button" onclick="document.getElementById('videoInput').click()"
                                    class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span class="text-xs sm:text-sm hidden xs:inline">Video</span>
                                </button>
                            </div>
                            <button type="submit"
                                class="px-4 sm:px-6 py-1.5 sm:py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm sm:text-base">
                                Post
                            </button>
                        </div>

                        @if($errors->any())
                            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 text-center">
            <p class="text-gray-600 mb-4">Join the conversation! Log in to create posts and interact with others.</p>
            <button onclick="openLoginModal()"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Log In to Post
            </button>
        </div>
    @endauth

    <!-- Posts Feed -->
    <div id="postsFeed" class="space-y-3 sm:space-y-6">
        @forelse($posts as $post)
            @include('components.post-card', ['post' => $post])
        @empty
            <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-200" id="emptyState">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                <p class="text-gray-500 text-lg">No posts yet</p>
                <p class="text-gray-400 mt-1">Be the first to share something!</p>
            </div>
        @endforelse

        <!-- Loading Indicator (Sentinel for infinite scroll) -->
        <div id="loadingIndicator" class="py-4 text-center min-h-[20px]">
            <div id="loadingSpinner" class="hidden items-center justify-center gap-2 text-gray-500">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span>Loading more posts...</span>
            </div>
        </div>

        <!-- End of Feed -->
        <div id="endOfFeed" class="hidden py-6 text-center">
            <p class="text-gray-400">You've reached the end of the feed</p>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="shareModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div
            class="bg-white rounded-t-xl sm:rounded-xl shadow-xl w-full sm:max-w-lg overflow-hidden max-h-[90vh] sm:max-h-none">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Share Post</h3>
                <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-4 sm:p-6">
                <textarea id="shareContent" placeholder="Add a comment to your share (optional)..."
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    rows="2"></textarea>

                <!-- Post Preview -->
                <div id="sharePostPreview"
                    class="mt-3 sm:mt-4 p-3 sm:p-4 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                    <!-- Will be filled dynamically -->
                </div>
            </div>
            <div class="flex gap-2 sm:gap-3 px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-200">
                <button onclick="closeShareModal()"
                    class="flex-1 sm:flex-none px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium text-sm sm:text-base">
                    Cancel
                </button>
                <button onclick="confirmShare()"
                    class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm sm:text-base">
                    Share
                </button>
            </div>
        </div>
    </div>

    <!-- Likes Modal -->
    <div id="likesModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div
            class="bg-white rounded-t-xl sm:rounded-xl shadow-xl w-full sm:max-w-md overflow-hidden max-h-[70vh] flex flex-col">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Likes</h3>
                <button onclick="closeLikesModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-3 sm:p-4 overflow-y-auto flex-1" id="likesModalContent">
                <div class="text-center py-4 text-gray-500 text-sm">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div id="editPostModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div
            class="bg-white rounded-t-xl sm:rounded-xl shadow-xl w-full sm:max-w-lg overflow-hidden max-h-[90vh] sm:max-h-none">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Edit Post</h3>
                <button onclick="closeEditPostModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <form id="editPostForm" class="p-4 sm:p-6">
                <input type="hidden" id="editPostId">
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Content</label>
                    <textarea id="editPostContent" rows="3"
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        placeholder="What's on your mind?"></textarea>
                </div>
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Location</label>
                    <input type="text" id="editPostLocation"
                        class="w-full px-3 sm:px-4 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Add location (optional)">
                </div>
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Privacy</label>
                    <select id="editPostPrivacy"
                        class="w-full px-3 sm:px-4 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="public">🌍 Public</option>
                        <option value="friends">👥 Friends Only</option>
                        <option value="private">🔒 Only Me</option>
                    </select>
                </div>
            </form>
            <div class="flex gap-2 sm:gap-3 px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-200">
                <button onclick="closeEditPostModal()"
                    class="flex-1 sm:flex-none px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium text-sm sm:text-base">Cancel</button>
                <button onclick="saveEditPost()"
                    class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm sm:text-base">Save</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-xl sm:rounded-xl shadow-xl w-full sm:max-w-sm overflow-hidden">
            <div class="p-4 sm:p-6 text-center">
                <div
                    class="w-12 h-12 sm:w-16 sm:h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1.5 sm:mb-2">Delete Post?</h3>
                <p class="text-gray-600 text-sm sm:text-base mb-4 sm:mb-6">This action cannot be undone.</p>
                <input type="hidden" id="deletePostId">
            </div>
            <div class="flex border-t border-gray-200">
                <button onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2.5 sm:py-3 text-gray-600 hover:bg-gray-50 transition-colors font-medium text-sm sm:text-base">Cancel</button>
                <button onclick="confirmDelete()"
                    class="flex-1 px-4 py-2.5 sm:py-3 text-red-600 hover:bg-red-50 transition-colors font-medium border-l border-gray-200 text-sm sm:text-base">Delete</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Initialize feed variables for infinite scroll -->
    <script>
        window.INITIAL_LAST_POST_ID = {{ $posts->last()?->id ?? 'null' }};
        window.INITIAL_HAS_MORE = {{ $posts->count() >= 10 ? 'true' : 'false' }};
        window.INITIAL_NEXT_PAGE = {{ auth()->check() && $posts->count() >= 10 ? 2 : 'null' }};
    </script>
    <script src="{{ asset('js/modules/posts.js') }}"></script>
    <script src="{{ asset('js/modules/reactions.js') }}"></script>
    <script src="{{ asset('js/modules/comments.js') }}"></script>
    <script src="{{ asset('js/modules/media.js') }}"></script>
    <script src="{{ asset('js/modules/feed.js') }}"></script>
@endpush