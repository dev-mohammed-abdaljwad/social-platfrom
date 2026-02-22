<div class="comments-section hidden mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-100" id="comments-{{ $post->id }}">

    @auth
        <form class="comment-form flex gap-2 sm:gap-3 mb-3 sm:mb-4 relative" data-post-id="{{ $post->id }}">
            <img src="{{ auth()->user()->avatar_url }}" alt="Profile"
                class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover flex-shrink-0">
            <div class="flex-1 flex flex-col gap-1.5 sm:gap-2 relative">
                <input type="hidden" name="parent_id" value="">
                <div class="input-wrapper relative w-full flex items-center bg-gray-100 rounded-full px-1">
                    <input type="text" name="content" placeholder="Write a comment..."
                        class="flex-1 px-3 sm:px-4 py-1.5 sm:py-2 bg-transparent text-xs sm:text-sm focus:outline-none"
                        style="min-width: 0;" required>
                    <button type="submit"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors text-xs sm:text-sm font-medium whitespace-nowrap ml-1">
                        Post
                    </button>
                </div>
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