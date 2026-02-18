<div class="flex gap-2 sm:gap-4">
    <a href="{{ route('profile.show', $post->user) }}" class="flex-shrink-0">
        <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->name }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
    </a>
    <div class="flex-1 min-w-0 flex items-center justify-between gap-2">
        <div class="min-w-0">
            <a href="{{ route('profile.show', $post->user) }}" class="font-semibold text-gray-800 hover:text-blue-600 text-sm sm:text-base truncate block">
                {{ $post->user->name }}
            </a>
            <p class="text-xs sm:text-sm text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
        </div>

        @if(auth()->check() && auth()->id() === $post->user_id)
            <x-post-menu :post="$post" />
        @else
            <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                </svg>
            </button>
        @endif
    </div>
</div>
