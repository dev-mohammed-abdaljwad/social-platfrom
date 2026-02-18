@php
    $userReaction = auth()->check() ? $post->reactions()->where('user_id', auth()->id())->first() : null;
    $reactionCounts = $post->reactions()->select('type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))->groupBy('type')->get();
    $totalReactions = $reactionCounts->sum('count');
@endphp

<div class="flex items-center justify-between sm:justify-start gap-2 sm:gap-6 mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-100">
    
    <!-- Reactions -->
    <div class="flex items-center gap-0.5 sm:gap-1 relative">
        <x-reaction-picker :post="$post" :userReaction="$userReaction" :reactionCounts="$reactionCounts" :totalReactions="$totalReactions" />
    </div>

    <!-- Comment Button -->
    <button class="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-blue-500 transition-colors comment-toggle-btn px-2 py-1.5 rounded-lg hover:bg-gray-100" 
            data-post-id="{{ $post->id }}" type="button">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <span class="comment-count text-xs sm:text-sm">{{ $post->comments_count ?? 0 }}</span>
    </button>

    <!-- Share Button -->
    <button class="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-green-500 transition-colors share-btn px-2 py-1.5 rounded-lg {{ $post->is_shared ? 'text-green-500' : '' }}" 
            data-post-id="{{ $post->id }}" type="button">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="{{ $post->is_shared ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
        </svg>
        <span class="shares-count text-xs sm:text-sm">{{ $post->shares_count ?? 0 }}</span>
    </button>

    <!-- Save Button -->
    @auth
    <button class="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-yellow-500 transition-colors save-btn sm:ml-auto px-2 py-1.5 rounded-lg {{ $post->is_saved ? 'text-yellow-500' : '' }}" 
            data-post-id="{{ $post->id }}" type="button">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="{{ $post->is_saved ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
        </svg>
    </button>
    @endauth

</div>
