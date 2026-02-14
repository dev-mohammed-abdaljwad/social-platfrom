@extends('layouts.app')

@section('title', 'Home - SocialTime')

@section('content')
    <!-- Create Post Card -->
    @auth
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 sm:p-4 mb-4 sm:mb-6">
        <form action="{{ route('posts.store') }}" method="POST" id="createPostForm" enctype="multipart/form-data">
            @csrf
            <div class="flex gap-2 sm:gap-4">
                <img 
                    src="{{ auth()->user()->avatar_url }}" 
                    alt="Profile" 
                    class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover flex-shrink-0"
                >
                <div class="flex-1 min-w-0">
                    <textarea 
                        name="content"
                        placeholder="What's on your mind?" 
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        rows="2"
                        id="postContent"
                    ></textarea>
                    
                    <!-- Media Preview -->
                    <div id="mediaPreview" class="hidden mt-3 space-y-2">
                        <!-- Image Preview -->
                        <div id="imagePreviewContainer" class="hidden relative">
                            <img id="imagePreview" class="max-h-64 rounded-lg object-cover" alt="Preview">
                            <button type="button" onclick="removeImage()" class="absolute top-2 right-2 bg-gray-800 bg-opacity-70 text-white rounded-full p-1 hover:bg-opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <!-- Video Preview -->
                        <div id="videoPreviewContainer" class="hidden relative">
                            <video id="videoPreview" class="max-h-64 rounded-lg w-full" controls></video>
                            <button type="button" onclick="removeVideo()" class="absolute top-2 right-2 bg-gray-800 bg-opacity-70 text-white rounded-full p-1 hover:bg-opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden File Inputs -->
                    <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="hidden" onchange="previewImage(this)">
                    <input type="file" id="videoInput" name="video" accept="video/mp4,video/mpeg,video/quicktime,video/webm" class="hidden" onchange="previewVideo(this)">

                    <!-- Location and Privacy -->
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-2 sm:mt-3">
                        <div class="flex-1">
                            <div class="relative">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 absolute left-2.5 sm:left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <input type="text" name="location" placeholder="Location" class="w-full pl-8 sm:pl-10 pr-3 py-1.5 sm:py-2 bg-gray-100 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="w-full sm:w-auto sm:min-w-[140px]">
                            <div class="relative">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 absolute left-2.5 sm:left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <select name="privacy" class="w-full pl-8 sm:pl-10 pr-6 sm:pr-8 py-1.5 sm:py-2 bg-gray-100 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer">
                                    <option value="public">🌍 Public</option>
                                    <option value="friends">👥 Friends</option>
                                    <option value="private">🔒 Only Me</option>
                                </select>
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400 absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-2 sm:mt-3">
                        <div class="flex gap-1 sm:gap-2">
                            <button type="button" onclick="document.getElementById('imageInput').click()" class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs sm:text-sm hidden xs:inline">Photo</span>
                            </button>
                            <button type="button" onclick="document.getElementById('videoInput').click()" class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs sm:text-sm hidden xs:inline">Video</span>
                            </button>
                        </div>
                        <button type="submit" class="px-4 sm:px-6 py-1.5 sm:py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm sm:text-base">
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
        <button onclick="openLoginModal()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            Log In to Post
        </button>
    </div>
    @endauth

    <!-- Posts Feed -->
    <div id="postsFeed" class="space-y-3 sm:space-y-6">
        @forelse($posts as $post)
        @include('partials.post-card', ['post' => $post])
        @empty
        <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-200" id="emptyState">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
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
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
    <div id="shareModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-xl sm:rounded-xl shadow-xl w-full sm:max-w-lg overflow-hidden max-h-[90vh] sm:max-h-none">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Share Post</h3>
                <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-4 sm:p-6">
                <textarea 
                    id="shareContent" 
                    placeholder="Add a comment to your share (optional)..." 
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    rows="2"
                ></textarea>
                
                <!-- Post Preview -->
                <div id="sharePostPreview" class="mt-3 sm:mt-4 p-3 sm:p-4 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                    <!-- Will be filled dynamically -->
                </div>
            </div>
            <div class="flex gap-2 sm:gap-3 px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-200">
                <button onclick="closeShareModal()" class="flex-1 sm:flex-none px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium text-sm sm:text-base">
                    Cancel
                </button>
                <button onclick="confirmShare()" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm sm:text-base">
                    Share
                </button>
            </div>
        </div>
    </div>

    <!-- Likes Modal -->
    <div id="likesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-xl sm:rounded-xl shadow-xl w-full sm:max-w-md overflow-hidden max-h-[70vh] flex flex-col">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Likes</h3>
                <button onclick="closeLikesModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-3 sm:p-4 overflow-y-auto flex-1" id="likesModalContent">
                <div class="text-center py-4 text-gray-500 text-sm">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div id="editPostModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-xl sm:rounded-xl shadow-xl w-full sm:max-w-lg overflow-hidden max-h-[90vh] sm:max-h-none">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Edit Post</h3>
                <button onclick="closeEditPostModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="editPostForm" class="p-4 sm:p-6">
                <input type="hidden" id="editPostId">
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Content</label>
                    <textarea id="editPostContent" rows="3" class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="What's on your mind?"></textarea>
                </div>
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Location</label>
                    <input type="text" id="editPostLocation" class="w-full px-3 sm:px-4 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add location (optional)">
                </div>
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Privacy</label>
                    <select id="editPostPrivacy" class="w-full px-3 sm:px-4 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="public">🌍 Public</option>
                        <option value="friends">👥 Friends Only</option>
                        <option value="private">🔒 Only Me</option>
                    </select>
                </div>
            </form>
            <div class="flex gap-2 sm:gap-3 px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-200">
                <button onclick="closeEditPostModal()" class="flex-1 sm:flex-none px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium text-sm sm:text-base">Cancel</button>
                <button onclick="saveEditPost()" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm sm:text-base">Save</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-xl sm:rounded-xl shadow-xl w-full sm:max-w-sm overflow-hidden">
            <div class="p-4 sm:p-6 text-center">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1.5 sm:mb-2">Delete Post?</h3>
                <p class="text-gray-600 text-sm sm:text-base mb-4 sm:mb-6">This action cannot be undone.</p>
                <input type="hidden" id="deletePostId">
            </div>
            <div class="flex border-t border-gray-200">
                <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 sm:py-3 text-gray-600 hover:bg-gray-50 transition-colors font-medium text-sm sm:text-base">Cancel</button>
                <button onclick="confirmDelete()" class="flex-1 px-4 py-2.5 sm:py-3 text-red-600 hover:bg-red-50 transition-colors font-medium border-l border-gray-200 text-sm sm:text-base">Delete</button>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
<script>
    // Media Preview Functions - Only one media type allowed at a time
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 10 * 1024 * 1024) {
                alert('Image size must be less than 10MB');
                input.value = '';
                return;
            }
            // Clear video if selected (only one media type allowed)
            removeVideo();
            
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').classList.remove('hidden');
                document.getElementById('mediaPreview').classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    function previewVideo(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 100 * 1024 * 1024) {
                alert('Video size must be less than 100MB');
                input.value = '';
                return;
            }
            // Clear image if selected (only one media type allowed)
            removeImage();
            
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('videoPreview').src = e.target.result;
                document.getElementById('videoPreviewContainer').classList.remove('hidden');
                document.getElementById('mediaPreview').classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    function removeImage() {
        document.getElementById('imageInput').value = '';
        document.getElementById('imagePreview').src = '';
        document.getElementById('imagePreviewContainer').classList.add('hidden');
        checkMediaPreview();
    }

    function removeVideo() {
        document.getElementById('videoInput').value = '';
        document.getElementById('videoPreview').src = '';
        document.getElementById('videoPreviewContainer').classList.add('hidden');
        checkMediaPreview();
    }

    function checkMediaPreview() {
        const imageHidden = document.getElementById('imagePreviewContainer').classList.contains('hidden');
        const videoHidden = document.getElementById('videoPreviewContainer').classList.contains('hidden');
        if (imageHidden && videoHidden) {
            document.getElementById('mediaPreview').classList.add('hidden');
        }
    }

    // Like button functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.setAttribute('data-listener', 'true');
        btn.addEventListener('click', async function() {
            @auth
            const postId = this.dataset.postId;
            const btn = this;
            const postCard = document.querySelector(`[data-post-id="${postId}"]`);
            const countSpan = postCard.querySelector('.likes-count');
            const svg = btn.querySelector('svg');
            const isLiked = btn.classList.contains('text-red-500');
            const currentCount = parseInt(countSpan.textContent) || 0;
            
            // Optimistic update - instant UI change
            if (isLiked) {
                btn.classList.remove('text-red-500');
                svg.setAttribute('fill', 'none');
                countSpan.textContent = Math.max(0, currentCount - 1);
            } else {
                btn.classList.add('text-red-500');
                svg.setAttribute('fill', 'currentColor');
                countSpan.textContent = currentCount + 1;
            }
            
            try {
                const response = await fetch(`/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    // Sync with server count
                    countSpan.textContent = data.likes_count;
                } else {
                    // Revert on error
                    btn.classList.toggle('text-red-500');
                    svg.setAttribute('fill', isLiked ? 'currentColor' : 'none');
                    countSpan.textContent = currentCount;
                }
            } catch (error) {
                console.error('Error liking post:', error);
                // Revert on error
                btn.classList.toggle('text-red-500');
                svg.setAttribute('fill', isLiked ? 'currentColor' : 'none');
                countSpan.textContent = currentCount;
            }
            @else
            openLoginModal();
            @endauth
        });
    });

    // View likes modal functionality
    document.querySelectorAll('.view-likes-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const postId = this.dataset.postId;
            openLikesModal(postId);
        });
    });

    function openLikesModal(postId) {
        document.getElementById('likesModal').classList.remove('hidden');
        document.getElementById('likesModalContent').innerHTML = '<div class="text-center py-4 text-gray-500">Loading...</div>';
        
        fetch(`/posts/${postId}/likes`)
            .then(response => response.json())
            .then(data => {
                if (data.likes && data.likes.length > 0) {
                    let html = '<div class="space-y-3">';
                    data.likes.forEach(like => {
                        html += `
                            <a href="/profile/${like.user.id}" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                <img src="${like.user.avatar_url || '/images/default-avatar.png'}" 
                                     alt="${like.user.name}" 
                                     class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <p class="font-semibold text-gray-800">${like.user.name}</p>
                                    <p class="text-sm text-gray-500">@${like.user.username || like.user.name.toLowerCase().replace(/\s+/g, '')}</p>
                                </div>
                            </a>
                        `;
                    });
                    html += '</div>';
                    document.getElementById('likesModalContent').innerHTML = html;
                } else {
                    document.getElementById('likesModalContent').innerHTML = '<div class="text-center py-4 text-gray-500">No likes yet</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching likes:', error);
                document.getElementById('likesModalContent').innerHTML = '<div class="text-center py-4 text-red-500">Error loading likes</div>';
            });
    }

    function closeLikesModal() {
        document.getElementById('likesModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('likesModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLikesModal();
        }
    });

    // Share modal state
    let currentSharePostId = null;
    let currentShareBtn = null;

    // Share button functionality - opens modal
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            @auth
            const postId = this.dataset.postId;
            currentSharePostId = postId;
            currentShareBtn = this;
            
            // Check if already shared - if so, unshare directly
            if (this.classList.contains('text-green-500')) {
                unsharePost(postId, this);
                return;
            }
            
            // Get post data for preview
            const postCard = document.querySelector(`[data-post-id="${postId}"]`);
            const userName = postCard.querySelector('a.font-semibold')?.textContent || 'User';
            const postContent = postCard.querySelector('.whitespace-pre-line')?.textContent || '';
            const postImage = postCard.querySelector('img[alt="Post image"]')?.src || '';
            
            // Build preview
            let previewHTML = `
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-semibold text-sm text-gray-800">${userName}</span>
                </div>
            `;
            if (postContent) {
                previewHTML += `<p class="text-gray-700 text-sm line-clamp-3">${postContent}</p>`;
            }
            if (postImage) {
                previewHTML += `<img src="${postImage}" class="mt-2 rounded max-h-32 object-cover" alt="Preview">`;
            }
            
            document.getElementById('sharePostPreview').innerHTML = previewHTML;
            document.getElementById('shareContent').value = '';
            document.getElementById('shareModal').classList.remove('hidden');
            @else
            openLoginModal();
            @endauth
        });
    });

    // Close share modal
    function closeShareModal() {
        document.getElementById('shareModal').classList.add('hidden');
        currentSharePostId = null;
        currentShareBtn = null;
    }

    // Confirm share with optional content
    async function confirmShare() {
        if (!currentSharePostId) return;
        
        const content = document.getElementById('shareContent').value.trim();
        
        try {
            const response = await fetch(`/posts/${currentSharePostId}/share`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content: content || null })
            });

            if (response.ok) {
                const data = await response.json();
                const countSpan = currentShareBtn.querySelector('.shares-count');
                const svg = currentShareBtn.querySelector('svg');
                
                countSpan.textContent = data.shares_count;
                
                if (data.shared) {
                    currentShareBtn.classList.add('text-green-500');
                    svg.setAttribute('fill', 'currentColor');
                }
                
                closeShareModal();
            }
        } catch (error) {
            console.error('Error sharing post:', error);
        }
    }

    // Unshare post (toggle off)
    async function unsharePost(postId, btn) {
        try {
            const response = await fetch(`/posts/${postId}/share`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                const data = await response.json();
                const countSpan = btn.querySelector('.shares-count');
                const svg = btn.querySelector('svg');
                
                countSpan.textContent = data.shares_count;
                
                if (!data.shared) {
                    btn.classList.remove('text-green-500');
                    svg.setAttribute('fill', 'none');
                }
            }
        } catch (error) {
            console.error('Error unsharing post:', error);
        }
    }

    // Comment toggle functionality
    document.querySelectorAll('.comment-toggle-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const postId = this.dataset.postId;
            const commentsSection = document.getElementById(`comments-${postId}`);
            const commentsList = document.getElementById(`comments-list-${postId}`);
            
            // Toggle visibility
            commentsSection.classList.toggle('hidden');
            
            // Load comments if opening
            if (!commentsSection.classList.contains('hidden')) {
                await loadComments(postId, commentsList);
            }
        });
    });

    // Load comments function
    async function loadComments(postId, container) {
        try {
            const response = await fetch(`/posts/${postId}/comments`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (data.success && data.comments.length > 0) {
                container.innerHTML = data.comments.map(comment => createCommentHTML(comment)).join('');
            } else {
                container.innerHTML = '<p class="text-gray-500 text-sm text-center py-2">No comments yet. Be the first to comment!</p>';
            }
        } catch (error) {
            console.error('Error loading comments:', error);
            container.innerHTML = '<p class="text-red-500 text-sm text-center py-2">Error loading comments</p>';
        }
    }

    // Create comment HTML
    function createCommentHTML(comment) {
        const deleteBtn = comment.is_owner ? `
            <button onclick="deleteComment(${comment.id})" class="text-gray-400 hover:text-red-500 text-xs">
                Delete
            </button>
        ` : '';
        
        const likedClass = comment.is_liked ? 'text-red-500' : 'text-gray-400';
        const heartFill = comment.is_liked ? 'fill-current' : '';
        
        return `
            <div class="flex gap-3 comment-item" id="comment-${comment.id}">
                <img src="${comment.user.avatar_url}" alt="${comment.user.name}" class="w-8 h-8 rounded-full object-cover">
                <div class="flex-1">
                    <div class="bg-gray-100 rounded-2xl px-4 py-2">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-sm text-gray-800">${comment.user.name}</span>
                            ${deleteBtn}
                        </div>
                        <p class="text-gray-700 text-sm">${comment.content}</p>
                    </div>
                    <div class="flex items-center gap-4 mt-1 ml-2">
                        <span class="text-xs text-gray-500">${comment.created_at}</span>
                        <button onclick="likeComment(${comment.id})" class="comment-like-btn flex items-center gap-1 text-xs ${likedClass} hover:text-red-500 transition-colors" data-comment-id="${comment.id}">
                            <svg class="w-4 h-4 ${heartFill}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span class="comment-likes-count">${comment.likes_count || 0}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // Comment form submit
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const postId = this.dataset.postId;
            const input = this.querySelector('input[name="content"]');
            const content = input.value.trim();
            
            if (!content) return;
            
            try {
                const response = await fetch(`/posts/${postId}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ content })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Clear input
                    input.value = '';
                    
                    // Add comment to list
                    const commentsList = document.getElementById(`comments-list-${postId}`);
                    const noCommentsMsg = commentsList.querySelector('p');
                    if (noCommentsMsg) noCommentsMsg.remove();
                    
                    commentsList.insertAdjacentHTML('afterbegin', createCommentHTML({
                        ...data.comment,
                        is_owner: true
                    }));
                    
                    // Update count
                    const postCard = document.querySelector(`[data-post-id="${postId}"]`);
                    const countSpan = postCard.querySelector('.comment-count');
                    countSpan.textContent = parseInt(countSpan.textContent) + 1;
                }
            } catch (error) {
                console.error('Error posting comment:', error);
            }
        });
    });

    // Delete comment function
    async function deleteComment(commentId) {
        if (!confirm('Delete this comment?')) return;
        
        try {
            const response = await fetch(`/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                const commentEl = document.getElementById(`comment-${commentId}`);
                const postCard = commentEl.closest('[data-post-id]');
                const countSpan = postCard.querySelector('.comment-count');
                countSpan.textContent = parseInt(countSpan.textContent) - 1;
                commentEl.remove();
            }
        } catch (error) {
            console.error('Error deleting comment:', error);
        }
    }

    // Like comment function
    async function likeComment(commentId) {
        @auth
        try {
            const response = await fetch(`/comments/${commentId}/like`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const commentEl = document.getElementById(`comment-${commentId}`);
                const likeBtn = commentEl.querySelector('.comment-like-btn');
                const countSpan = likeBtn.querySelector('.comment-likes-count');
                const svg = likeBtn.querySelector('svg');
                
                countSpan.textContent = data.likes_count;
                
                if (data.liked) {
                    likeBtn.classList.remove('text-gray-400');
                    likeBtn.classList.add('text-red-500');
                    svg.classList.add('fill-current');
                } else {
                    likeBtn.classList.remove('text-red-500');
                    likeBtn.classList.add('text-gray-400');
                    svg.classList.remove('fill-current');
                }
            }
        } catch (error) {
            console.error('Error liking comment:', error);
        }
        @else
        openLoginModal();
        @endauth
    }

    // Infinite Scroll
    let lastPostId = {{ $posts->last()?->id ?? 'null' }};
    let isLoading = false;
    let hasMore = {{ $posts->count() >= 10 ? 'true' : 'false' }};
    
    const postsFeed = document.getElementById('postsFeed');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const endOfFeed = document.getElementById('endOfFeed');
    
    // Intersection Observer for infinite scroll
    const observerCallback = async (entries) => {
        const entry = entries[0];
        if (entry.isIntersecting && !isLoading && hasMore) {
            await loadMorePosts();
        }
    };
    
    const observer = new IntersectionObserver(observerCallback, {
        root: null,
        rootMargin: '200px',
        threshold: 0
    });
    
    // Start observing the loading indicator
    if (loadingIndicator && hasMore) {
        observer.observe(loadingIndicator);
    }
    
    async function loadMorePosts() {
        if (isLoading || !hasMore || !lastPostId) return;
        
        isLoading = true;
        loadingSpinner.classList.remove('hidden');
        loadingSpinner.classList.add('flex');
        
        try {
            const response = await fetch(`/posts/feed?last_id=${lastPostId}&limit=10`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.html && data.html.trim()) {
                    // Insert new posts before the loading indicator
                    loadingIndicator.insertAdjacentHTML('beforebegin', data.html);
                    
                    // Re-attach event listeners to new posts
                    attachEventListenersToNewPosts();
                    
                    lastPostId = data.last_id;
                    hasMore = data.has_more;
                } else {
                    hasMore = false;
                }
                
                if (!hasMore) {
                    loadingIndicator.classList.add('hidden');
                    endOfFeed.classList.remove('hidden');
                    observer.disconnect();
                }
            }
        } catch (error) {
            console.error('Error loading posts:', error);
        } finally {
            isLoading = false;
            loadingSpinner.classList.add('hidden');
            loadingSpinner.classList.remove('flex');
        }
    }
    
    function attachEventListenersToNewPosts() {
        // Re-attach like button listeners
        document.querySelectorAll('.like-btn:not([data-listener])').forEach(btn => {
            btn.setAttribute('data-listener', 'true');
            btn.addEventListener('click', async function() {
                @auth
                const postId = this.dataset.postId;
                const btn = this;
                const postCard = document.querySelector(`[data-post-id="${postId}"]`);
                const countSpan = postCard.querySelector('.likes-count');
                const svg = btn.querySelector('svg');
                const isLiked = btn.classList.contains('text-red-500');
                const currentCount = parseInt(countSpan.textContent) || 0;
                
                // Optimistic update - instant UI change
                if (isLiked) {
                    btn.classList.remove('text-red-500');
                    svg.setAttribute('fill', 'none');
                    countSpan.textContent = Math.max(0, currentCount - 1);
                } else {
                    btn.classList.add('text-red-500');
                    svg.setAttribute('fill', 'currentColor');
                    countSpan.textContent = currentCount + 1;
                }
                
                try {
                    const response = await fetch(`/posts/${postId}/like`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        countSpan.textContent = data.likes_count;
                    } else {
                        // Revert on error
                        btn.classList.toggle('text-red-500');
                        svg.setAttribute('fill', isLiked ? 'currentColor' : 'none');
                        countSpan.textContent = currentCount;
                    }
                } catch (error) {
                    console.error('Error liking post:', error);
                    // Revert on error
                    btn.classList.toggle('text-red-500');
                    svg.setAttribute('fill', isLiked ? 'currentColor' : 'none');
                    countSpan.textContent = currentCount;
                }
                @else
                openLoginModal();
                @endauth
            });
        });
        
        // Re-attach comment toggle listeners
        document.querySelectorAll('.comment-toggle-btn:not([data-listener])').forEach(btn => {
            btn.setAttribute('data-listener', 'true');
            btn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                const commentsSection = document.getElementById(`comments-${postId}`);
                const commentsList = document.getElementById(`comments-list-${postId}`);
                
                commentsSection.classList.toggle('hidden');
                
                if (!commentsSection.classList.contains('hidden') && commentsList.dataset.loaded !== 'true') {
                    loadComments(postId, commentsList);
                    commentsList.dataset.loaded = 'true';
                }
            });
        });
        
        // Re-attach share button listeners
        document.querySelectorAll('.share-btn:not([data-listener])').forEach(btn => {
            btn.setAttribute('data-listener', 'true');
            btn.addEventListener('click', function() {
                @auth
                const postId = this.dataset.postId;
                currentSharePostId = postId;
                currentShareBtn = this;
                
                if (this.classList.contains('text-green-500')) {
                    unsharePost(postId, this);
                    return;
                }
                
                const postCard = document.querySelector(`[data-post-id="${postId}"]`);
                const userName = postCard.querySelector('a.font-semibold')?.textContent || 'User';
                const postContent = postCard.querySelector('.whitespace-pre-line')?.textContent || '';
                const postImage = postCard.querySelector('img[alt="Post image"]')?.src || '';
                
                let previewHTML = `<div class="flex items-center gap-2 mb-2"><span class="font-semibold text-sm text-gray-800">${userName}</span></div>`;
                if (postContent) {
                    previewHTML += `<p class="text-gray-700 text-sm line-clamp-3">${postContent}</p>`;
                }
                if (postImage) {
                    previewHTML += `<img src="${postImage}" class="mt-2 rounded max-h-32 object-cover" alt="Preview">`;
                }
                
                document.getElementById('sharePostPreview').innerHTML = previewHTML;
                document.getElementById('shareContent').value = '';
                document.getElementById('shareModal').classList.remove('hidden');
                @else
                openLoginModal();
                @endauth
            });
        });
        
        // Re-attach comment form listeners
        document.querySelectorAll('.comment-form:not([data-listener])').forEach(form => {
            form.setAttribute('data-listener', 'true');
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const postId = this.dataset.postId;
                const input = this.querySelector('input[name="content"]');
                const content = input.value.trim();
                
                if (!content) return;
                
                try {
                    const response = await fetch(`/posts/${postId}/comments`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ content })
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        input.value = '';
                        
                        const commentsList = document.getElementById(`comments-list-${postId}`);
                        const commentHtml = createCommentHTML(data.comment);
                        commentsList.insertAdjacentHTML('afterbegin', commentHtml);
                        
                        const commentCount = document.querySelector(`[data-post-id="${postId}"] .comment-count`);
                        if (commentCount) {
                            commentCount.textContent = parseInt(commentCount.textContent) + 1;
                        }
                    }
                } catch (error) {
                    console.error('Error posting comment:', error);
                }
            });
        });

        // Re-attach save button listeners
        document.querySelectorAll('.save-btn:not([data-listener])').forEach(btn => {
            btn.setAttribute('data-listener', 'true');
            btn.addEventListener('click', async function() {
                const postId = this.dataset.postId;
                const btn = this;
                try {
                    const response = await fetch(`/posts/${postId}/save`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        const svg = btn.querySelector('svg');
                        
                        if (data.saved) {
                            btn.classList.add('text-yellow-500');
                            svg.setAttribute('fill', 'currentColor');
                        } else {
                            btn.classList.remove('text-yellow-500');
                            svg.setAttribute('fill', 'none');
                        }
                    }
                } catch (error) {
                    console.error('Error saving post:', error);
                }
            });
        });
    }
    
    // Mark existing buttons as having listeners
    document.querySelectorAll('.like-btn, .comment-toggle-btn, .share-btn, .save-btn, .comment-form').forEach(el => {
        el.setAttribute('data-listener', 'true');
    });

    // Save button functionality
    document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const postId = this.dataset.postId;
            const btn = this;
            try {
                const response = await fetch(`/posts/${postId}/save`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const svg = btn.querySelector('svg');
                    
                    if (data.saved) {
                        btn.classList.add('text-yellow-500');
                        svg.setAttribute('fill', 'currentColor');
                    } else {
                        btn.classList.remove('text-yellow-500');
                        svg.setAttribute('fill', 'none');
                    }
                }
            } catch (error) {
                console.error('Error saving post:', error);
            }
        });
    });

    // Post menu toggle functionality
    document.addEventListener('click', function(e) {
        const menuBtn = e.target.closest('.post-menu-btn');
        if (menuBtn) {
            const postId = menuBtn.dataset.postId;
            const menu = document.querySelector(`.post-menu[data-post-id="${postId}"]`);
            
            // Close all other menus first
            document.querySelectorAll('.post-menu').forEach(m => {
                if (m !== menu) m.classList.add('hidden');
            });
            
            menu.classList.toggle('hidden');
            return;
        }
        
        // Close menus when clicking outside
        if (!e.target.closest('.post-menu')) {
            document.querySelectorAll('.post-menu').forEach(m => m.classList.add('hidden'));
        }
    });

    // Edit post button click handler
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.edit-post-btn');
        if (editBtn) {
            const postId = editBtn.dataset.postId;
            const content = editBtn.dataset.content || '';
            const location = editBtn.dataset.location || '';
            const privacy = editBtn.dataset.privacy || 'public';
            
            openEditPostModal(postId, content, location, privacy);
            
            // Close the menu
            document.querySelectorAll('.post-menu').forEach(m => m.classList.add('hidden'));
        }
    });

    // Delete post button click handler
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-post-btn');
        if (deleteBtn) {
            const postId = deleteBtn.dataset.postId;
            openDeleteModal(postId);
            
            // Close the menu
            document.querySelectorAll('.post-menu').forEach(m => m.classList.add('hidden'));
        }
    });

    // Edit Post Modal functions
    function openEditPostModal(postId, content, location, privacy) {
        document.getElementById('editPostId').value = postId;
        document.getElementById('editPostContent').value = content;
        document.getElementById('editPostLocation').value = location;
        document.getElementById('editPostPrivacy').value = privacy;
        document.getElementById('editPostModal').classList.remove('hidden');
    }

    function closeEditPostModal() {
        document.getElementById('editPostModal').classList.add('hidden');
    }

    async function saveEditPost() {
        const postId = document.getElementById('editPostId').value;
        const content = document.getElementById('editPostContent').value;
        const location = document.getElementById('editPostLocation').value;
        const privacy = document.getElementById('editPostPrivacy').value;
        
        try {
            const response = await fetch(`/posts/${postId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content, location, privacy })
            });
            
            if (response.ok) {
                const data = await response.json();
                
                // Update post content in the DOM
                const postCard = document.querySelector(`[data-post-id="${postId}"]`);
                const postContent = postCard.querySelector('.post-content');
                if (postContent) {
                    postContent.textContent = content;
                }
                
                // Update the edit button data attributes
                const editBtn = postCard.querySelector('.edit-post-btn');
                if (editBtn) {
                    editBtn.dataset.content = content;
                    editBtn.dataset.location = location;
                    editBtn.dataset.privacy = privacy;
                }
                
                closeEditPostModal();
            } else {
                const error = await response.json();
                alert(error.message || 'Failed to update post');
            }
        } catch (error) {
            console.error('Error updating post:', error);
            alert('Error updating post');
        }
    }

    // Delete Modal functions
    function openDeleteModal(postId) {
        document.getElementById('deletePostId').value = postId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    async function confirmDelete() {
        const postId = document.getElementById('deletePostId').value;
        
        try {
            const response = await fetch(`/posts/${postId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                // Remove the post from the DOM
                const postCard = document.querySelector(`[data-post-id="${postId}"]`);
                if (postCard) {
                    postCard.remove();
                }
                closeDeleteModal();
            } else {
                const error = await response.json();
                alert(error.message || 'Failed to delete post');
            }
        } catch (error) {
            console.error('Error deleting post:', error);
            alert('Error deleting post');
        }
    }

    // Close modals when clicking outside
    document.getElementById('editPostModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditPostModal();
    });
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // Scroll to post if coming from notification (run after page fully loaded)
    window.addEventListener('load', async function() {
        const urlParams = new URLSearchParams(window.location.search);
        const postId = urlParams.get('post');
        
        if (postId) {
            // Helper function to load comments inline
            const fetchComments = async (postId, container) => {
                try {
                    const response = await fetch(`/posts/${postId}/comments`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    
                    if (data.success && data.comments.length > 0) {
                        container.innerHTML = data.comments.map(comment => createCommentHTML(comment)).join('');
                    } else {
                        container.innerHTML = '<p class="text-gray-500 text-sm text-center py-2">No comments yet. Be the first to comment!</p>';
                    }
                } catch (error) {
                    console.error('Error loading comments:', error);
                    container.innerHTML = '<p class="text-red-500 text-sm text-center py-2">Error loading comments</p>';
                }
            };
            
            // Wait for posts to load and try scrolling
            const attemptScroll = async () => {
                const postElement = document.getElementById(`post-${postId}`);
                if (postElement) {
                    postElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    postElement.classList.add('ring-2', 'ring-blue-500', 'ring-opacity-50');
                    setTimeout(() => {
                        postElement.classList.remove('ring-2', 'ring-blue-500', 'ring-opacity-50');
                    }, 3000);
                    
                    // Handle comment hash - open and scroll to comment
                    if (window.location.hash.startsWith('#comment-')) {
                        const commentId = window.location.hash.replace('#comment-', '');
                        const commentsSection = document.getElementById(`comments-${postId}`);
                        const commentsList = document.getElementById(`comments-list-${postId}`);
                        
                        if (commentsSection && commentsList) {
                            commentsSection.classList.remove('hidden');
                            
                            // Load comments first
                            await fetchComments(postId, commentsList);
                            
                            // Then scroll to specific comment
                            setTimeout(() => {
                                const commentElement = document.getElementById(`comment-${commentId}`);
                                if (commentElement) {
                                    commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    commentElement.classList.add('bg-blue-50');
                                    setTimeout(() => commentElement.classList.remove('bg-blue-50'), 3000);
                                }
                            }, 300);
                        }
                    }
                    
                    // Handle like notification - show likes modal
                    if (window.location.hash === '#likes') {
                        const viewLikesBtn = postElement.querySelector('.view-likes-btn');
                        if (viewLikesBtn) {
                            viewLikesBtn.click();
                        }
                    }
                    
                    return true;
                }
                return false;
            };
            
            // Try after a brief delay to ensure posts are rendered
            setTimeout(async () => {
                const found = await attemptScroll();
                if (!found) {
                    setTimeout(attemptScroll, 1500);
                }
            }, 500);
        }
    });
</script>
@endpush
