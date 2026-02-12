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
                <button class="flex items-center gap-2 text-gray-600 hover:text-blue-500 transition-colors comment-toggle-btn" data-post-id="{{ $post->id }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="comment-count">{{ $post->comments->count() }}</span>
                </button>
                <button class="flex items-center gap-2 text-gray-600 hover:text-green-500 transition-colors share-btn {{ auth()->check() && $post->isSharedBy(auth()->user()) ? 'text-green-500' : '' }}" data-post-id="{{ $post->id }}">
                    <svg class="w-5 h-5" fill="{{ auth()->check() && $post->isSharedBy(auth()->user()) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                    <span class="shares-count">{{ $post->shares->count() }}</span>
                </button>
            </div>
            
            <!-- Comments Section -->
            <div class="comments-section hidden mt-4 pt-4 border-t border-gray-100" id="comments-{{ $post->id }}">
                <!-- Comment Form -->
                @auth
                <form class="comment-form flex gap-3 mb-4" data-post-id="{{ $post->id }}">
                    <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="w-8 h-8 rounded-full object-cover">
                    <div class="flex-1 flex gap-2">
                        <input type="text" 
                               name="content" 
                               placeholder="Write a comment..." 
                               class="flex-1 px-4 py-2 bg-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors text-sm font-medium">
                            Post
                        </button>
                    </div>
                </form>
                @else
                <p class="text-gray-500 text-sm mb-4">
                    <button onclick="openLoginModal()" class="text-blue-600 hover:underline">Log in</button> to comment
                </p>
                @endauth
                
                <!-- Comments List -->
                <div class="comments-list space-y-3" id="comments-list-{{ $post->id }}">
                    <div class="text-center py-2">
                        <span class="text-gray-400 text-sm">Loading comments...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
