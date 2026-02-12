@extends('layouts.app')

@section('title', 'Home - SocialHub')

@section('content')
    <!-- Create Post Card -->
    @auth
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form action="{{ route('posts.store') }}" method="POST" id="createPostForm" enctype="multipart/form-data">
            @csrf
            <div class="flex gap-4">
                <img 
                    src="{{ auth()->user()->avatar_url }}" 
                    alt="Profile" 
                    class="w-10 h-10 rounded-full object-cover"
                >
                <div class="flex-1">
                    <textarea 
                        name="content"
                        placeholder="What's on your mind, {{ auth()->user()->name }}?" 
                        class="w-full px-4 py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        rows="3"
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

                    <div class="flex items-center justify-between mt-3">
                        <div class="flex gap-2">
                            <button type="button" onclick="document.getElementById('imageInput').click()" class="flex items-center gap-2 px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm">Photo</span>
                            </button>
                            <button type="button" onclick="document.getElementById('videoInput').click()" class="flex items-center gap-2 px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm">Video</span>
                            </button>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
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
    <div id="postsFeed" class="space-y-6">
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
    <div id="shareModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Share Post</h3>
                <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <textarea 
                    id="shareContent" 
                    placeholder="Add a comment to your share (optional)..." 
                    class="w-full px-4 py-3 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    rows="3"
                ></textarea>
                
                <!-- Post Preview -->
                <div id="sharePostPreview" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <!-- Will be filled dynamically -->
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
                <button onclick="closeShareModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmShare()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                    Share
                </button>
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
        btn.addEventListener('click', async function() {
            @auth
            const postId = this.dataset.postId;
            const btn = this;
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
                    const countSpan = btn.querySelector('span');
                    const svg = btn.querySelector('svg');
                    let count = parseInt(countSpan.textContent);
                    
                    if (data.liked) {
                        countSpan.textContent = count + 1;
                        btn.classList.add('text-red-500');
                        svg.setAttribute('fill', 'currentColor');
                    } else {
                        countSpan.textContent = count - 1;
                        btn.classList.remove('text-red-500');
                        svg.setAttribute('fill', 'none');
                    }
                }
            } catch (error) {
                console.error('Error liking post:', error);
            }
            @else
            openLoginModal();
            @endauth
        });
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
                        const countSpan = btn.querySelector('span');
                        const svg = btn.querySelector('svg');
                        
                        countSpan.textContent = data.likes_count;
                        
                        if (data.liked) {
                            btn.classList.add('text-red-500');
                            svg.setAttribute('fill', 'currentColor');
                        } else {
                            btn.classList.remove('text-red-500');
                            svg.setAttribute('fill', 'none');
                        }
                    }
                } catch (error) {
                    console.error('Error liking post:', error);
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
                    loadComments(postId);
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
    }
    
    // Mark existing buttons as having listeners
    document.querySelectorAll('.like-btn, .comment-toggle-btn, .share-btn, .comment-form').forEach(el => {
        el.setAttribute('data-listener', 'true');
    });
</script>
@endpush
