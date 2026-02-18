<div class="relative reaction-picker-container" data-post-id="{{ $post->id }}">
    
    <!-- Main Reaction Button -->
    <button class="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-blue-500 transition-colors reaction-btn-main px-2 py-1.5 rounded-lg hover:bg-gray-100 {{ $userReaction ? 'text-blue-600 font-semibold' : '' }}" 
            data-post-id="{{ $post->id }}" type="button">
        @if($userReaction)
            <span class="text-lg reaction-emoji">{{ \App\Enums\ReactionTypeEnum::tryFrom($userReaction->type)->emoji() }}</span>
            <span class="text-xs sm:text-sm capitalize reaction-label">{{ $userReaction->type }}</span>
        @else
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.708C19.746 10 20.5 10.811 20.5 11.812c0 .387-.121.76-.346 1.07l-2.44 3.356c-.311.43-.802.682-1.324.682H8.5V10c0-.828.672-1.5 1.5-1.5h.5V4.667c0-.92.747-1.667 1.667-1.667h1.666c.92 0 1.667.747 1.667 1.667V10zM8.5 10H5.5c-.828 0-1.5.672-1.5 1.5v5c0 .828.672 1.5 1.5 1.5h3V10z"></path>
            </svg>
            <span class="text-xs sm:text-sm">Like</span>
        @endif
    </button>

    <!-- Picker -->
    <div class="absolute bottom-full left-0 mb-2 bg-white rounded-full shadow-xl border border-gray-100 p-2 hidden reaction-picker z-20 flex items-center gap-1" id="picker-{{ $post->id }}">
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

    <!-- Reactions Count -->
    <button class="text-gray-600 hover:text-blue-500 hover:underline text-xs sm:text-sm view-reactions-btn flex items-center gap-1 px-2 py-1.5 rounded-lg hover:bg-gray-100" data-post-id="{{ $post->id }}" type="button">
        <div class="flex -space-x-1">
            @foreach($reactionCounts->sortByDesc('count')->take(3) as $count)
                <span class="text-xs">{{ \App\Enums\ReactionTypeEnum::tryFrom($count->type)->emoji() }}</span>
            @endforeach
        </div>
        <span class="reactions-count">{{ $totalReactions > 0 ? $totalReactions : '' }}</span>
    </button>

</div>
