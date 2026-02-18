<div class="comments-section hidden mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-100" id="comments-{{ $post->id }}">
    
    @auth
    <form class="comment-form flex gap-2 sm:gap-3 mb-3 sm:mb-4" data-post-id="{{ $post->id }}">
        <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover flex-shrink-0">
        <div class="flex-1 flex gap-1.5 sm:gap-2">
            <input type="text" name="content" placeholder="Write a comment..." 
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

    <div class="comments-list space-y-3" id="comments-list-{{ $post->id }}">
        <div class="text-center py-2">
            <span class="text-gray-400 text-sm">Loading comments...</span>
        </div>
    </div>

</div>
