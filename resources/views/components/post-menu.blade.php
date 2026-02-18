<div class="relative">
    <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors post-menu-btn" data-post-id="{{ $post->id }}">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"></path>
        </svg>
    </button>
    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 hidden z-10 post-menu" data-post-id="{{ $post->id }}">
        <x-menu-button type="edit" :post="$post" />
        <x-menu-button type="delete" :post="$post" />
    </div>
</div>
