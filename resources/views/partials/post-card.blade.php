<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 sm:p-4" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">
    <div class="flex gap-2 sm:gap-4">
        <a href="{{ route('profile.show', $post->user) }}" class="flex-shrink-0">
            <img src="{{ $post->user->avatar_url }}" 
                 alt="{{ $post->user->name }}" 
                 class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
        </a>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <a href="{{ route('profile.show', $post->user) }}" class="font-semibold text-gray-800 hover:text-blue-600 text-sm sm:text-base truncate block">{{ $post->user->name }}</a>
                    <p class="text-xs sm:text-sm text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                </div>
                @if(auth()->check() && auth()->id() === $post->user_id)
                <div class="relative">
                    <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors post-menu-btn" data-post-id="{{ $post->id }}">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                        </svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 hidden z-10 post-menu" data-post-id="{{ $post->id }}">
                        <button class="w-full px-4 py-2 text-left text-gray-700 hover:bg-gray-100 rounded-t-lg edit-post-btn" 
                                data-post-id="{{ $post->id }}" 
                                data-content="{{ $post->content }}"
                                data-location="{{ $post->location }}"
                                data-privacy="{{ $post->privacy?->value }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Post
                            </span>
                        </button>
                        <button class="w-full px-4 py-2 text-left text-red-600 hover:bg-red-50 rounded-b-lg delete-post-btn" data-post-id="{{ $post->id }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Post
                            </span>
                        </button>
                    </div>
                </div>
                @else
                <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>
                @endif
            </div>
            
            <!-- Post Content -->
            @if($post->content)
            <p class="mt-2 sm:mt-3 text-gray-700 whitespace-pre-line post-content text-sm sm:text-base">{{ $post->content }}</p>
            @endif
            
            <!-- Post Image -->
            @if($post->image_url)
            <img src="{{ $post->image_url }}" alt="Post image" class="mt-2 sm:mt-3 rounded-lg max-h-72 sm:max-h-96 w-full object-cover">
            @endif
            
            <!-- Post Video -->
            @if($post->video_url)
            <video controls class="mt-2 sm:mt-3 rounded-lg max-h-72 sm:max-h-96 w-full">
                <source src="{{ $post->video_url }}" type="video/mp4">
            </video>
            @endif
            
            <!-- Post Location -->
            @if($post->location)
            <p class="mt-2 text-xs sm:text-sm text-gray-500 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                {{ $post->location }}
            </p>
            @endif
            
          <!-- Post Actions -->
<div class="flex items-center justify-between sm:justify-start gap-2 sm:gap-6 mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-100">
    <div class="flex items-center gap-0.5 sm:gap-1 relative">
        @php
            $userReaction = auth()->check() ? $post->reactions()->where('user_id', auth()->id())->first() : null;
            $reactionCounts = $post->reactions()->select('type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))->groupBy('type')->get();
            $totalReactions = $reactionCounts->sum('count');
        @endphp
        
        <div class="relative reaction-picker-container" data-post-id="{{ $post->id }}">
            <!-- Main Reaction Button -->
            <button class="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-blue-500 transition-colors reaction-btn-main px-2 py-1.5 rounded-lg hover:bg-gray-100 {{ $userReaction ? 'text-blue-600 font-semibold' : '' }}" 
                    data-post-id="{{ $post->id }}"
                    type="button">
                @if($userReaction)
                    <span class="text-lg reaction-emoji">{{ \App\Enums\ReactionTypeEnum::from($userReaction->type->value)->emoji() }}</span>
                    <span class="text-xs sm:text-sm capitalize reaction-label">{{ $userReaction->type->value }}</span>
                @else
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.708C19.746 10 20.5 10.811 20.5 11.812c0 .387-.121.76-.346 1.07l-2.44 3.356c-.311.43-.802.682-1.324.682H8.5V10c0-.828.672-1.5 1.5-1.5h.5V4.667c0-.92.747-1.667 1.667-1.667h1.666c.92 0 1.667.747 1.667 1.667V10zM8.5 10H5.5c-.828 0-1.5.672-1.5 1.5v5c0 .828.672 1.5 1.5 1.5h3V10z"></path>
                    </svg>
                    <span class="text-xs sm:text-sm">Like</span>
                @endif
            </button>

            <!-- Reaction Picker - Stays visible when opened -->
            <div class="absolute bottom-full left-0 mb-2 bg-white rounded-full shadow-xl border border-gray-100 p-2 hidden reaction-picker z-20 flex items-center gap-1"
                 id="picker-{{ $post->id }}">
                @foreach(\App\Enums\ReactionTypeEnum::cases() as $case)
                    <button class="p-2 hover:scale-125 transition-transform reaction-option" 
                            data-post-id="{{ $post->id }}" 
                            data-type="{{ $case->value }}"
                            title="{{ ucfirst($case->value) }}"
                            type="button">
                        <span class="text-xl sm:text-2xl cursor-pointer">{{ $case->emoji() }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Reactions Count & Icons -->
        <button class="text-gray-600 hover:text-blue-500 hover:underline text-xs sm:text-sm view-reactions-btn flex items-center gap-1 px-2 py-1.5 rounded-lg hover:bg-gray-100" 
                data-post-id="{{ $post->id }}"
                type="button">
            <div class="flex -space-x-1">
                @foreach($reactionCounts->sortByDesc('count')->take(3) as $count)
                    <span class="text-xs">{{ \App\Enums\ReactionTypeEnum::from($count->type->value)->emoji() }}</span>
                @endforeach
            </div>
            <span class="reactions-count">{{ $totalReactions > 0 ? $totalReactions : '' }}</span>
        </button>
    </div>

    <!-- Rest of buttons remain the same -->
    <button class="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-blue-500 transition-colors comment-toggle-btn px-2 py-1.5 rounded-lg hover:bg-gray-100" data-post-id="{{ $post->id }}" type="button">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <span class="comment-count text-xs sm:text-sm">{{ $post->comments_count ?? 0 }}</span>
    </button>
    <button class="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-green-500 transition-colors share-btn px-2 py-1.5 rounded-lg hover:bg-gray-100 {{ $post->is_shared ? 'text-green-500' : '' }}" data-post-id="{{ $post->id }}" type="button">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="{{ $post->is_shared ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
        </svg>
        <span class="shares-count text-xs sm:text-sm">{{ $post->shares_count ?? 0 }}</span>
    </button>
    @auth
    <button class="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-yellow-500 transition-colors save-btn sm:ml-auto px-2 py-1.5 rounded-lg hover:bg-gray-100 {{ $post->is_saved ? 'text-yellow-500' : '' }}" data-post-id="{{ $post->id }}" type="button">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="{{ $post->is_saved ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
        </svg>
    </button>
    @endauth
</div>
            <!-- Comments Section -->
            <div class="comments-section hidden mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-100" id="comments-{{ $post->id }}">
                <!-- Comment Form -->
                @auth
                <form class="comment-form flex gap-2 sm:gap-3 mb-3 sm:mb-4" data-post-id="{{ $post->id }}">
                    <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover flex-shrink-0">
                    <div class="flex-1 flex gap-1.5 sm:gap-2">
                        <input type="text" 
                               name="content" 
                               placeholder="Write a comment..." 
                               class="flex-1 px-3 sm:px-4 py-1.5 sm:py-2 bg-gray-100 rounded-full text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                        <button type="submit" class="px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors text-xs sm:text-sm font-medium">
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