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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4" data-post-id="{{ $post->id }}">
            <div class="flex gap-4">
                <a href="{{ route('profile.show', $post->user) }}">
                    <img src="{{ $post->user->avatar_url }}" 
                         alt="{{ $post->user->name }}" 
                         class="w-10 h-10 rounded-full object-cover">
                </a>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <a href="{{ route('profile.show', $post->user) }}" class="font-semibold text-gray-800 hover:text-blue-600">{{ $post->user->name }}</a>
                            <p class="text-sm text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                        </div>
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Post Content -->
                    @if($post->content)
                    <p class="mt-3 text-gray-700 whitespace-pre-line">{{ $post->content }}</p>
                    @endif
                    
                    <!-- Post Image -->
                    @if($post->image_url)
                    <img src="{{ $post->image_url }}" alt="Post image" class="mt-3 rounded-lg max-h-96 w-full object-cover">
                    @endif
                    
                    <!-- Post Video -->
                    @if($post->video_url)
                    <video controls class="mt-3 rounded-lg max-h-96 w-full">
                        <source src="{{ $post->video_url }}" type="video/mp4">
                    </video>
                    @endif
                    
                    <!-- Post Location -->
                    @if($post->location)
                    <p class="mt-2 text-sm text-gray-500 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $post->location }}
                    </p>
                    @endif
                    
                    <!-- Post Actions -->
                    <div class="flex items-center gap-6 mt-4 pt-4 border-t border-gray-100">
                        <button class="flex items-center gap-2 text-gray-600 hover:text-red-500 transition-colors like-btn {{ auth()->check() && $post->isLikedBy(auth()->user()) ? 'text-red-500' : '' }}" data-post-id="{{ $post->id }}">
                            <svg class="w-5 h-5" fill="{{ auth()->check() && $post->isLikedBy(auth()->user()) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span>{{ $post->likes->count() }}</span>
                        </button>
                        <button class="flex items-center gap-2 text-gray-600 hover:text-blue-500 transition-colors comment-btn" data-post-id="{{ $post->id }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span>{{ $post->comments->count() }}</span>
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
        @empty
        <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-200">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <p class="text-gray-500 text-lg">No posts yet</p>
            <p class="text-gray-400 mt-1">Be the first to share something!</p>
        </div>
        @endforelse
        
        <!-- Pagination -->
        @if($posts->hasPages())
        <div class="mt-6">
            {{ $posts->links() }}
        </div>
        @endif
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
</script>
@endpush
