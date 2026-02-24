@if($post->content)
    <p class="mt-2 sm:mt-3 text-gray-700 whitespace-pre-line post-content text-sm sm:text-base">
        @mentions([$post->content, $post->mentions->pluck('mentionedUser')])
    </p>
@endif

@if($post->image_url)
    <img src="{{ $post->image_url }}" alt="Post image"
        class="mt-2 sm:mt-3 rounded-lg max-h-72 sm:max-h-96 w-full object-cover">
@endif

@if($post->video_url)
    <video controls class="mt-2 sm:mt-3 rounded-lg max-h-72 sm:max-h-96 w-full">
        <source src="{{ $post->video_url }}" type="video/mp4">
    </video>
@endif

@if($post->location)
    <p class="mt-2 text-xs sm:text-sm text-gray-500 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z">
            </path>
        </svg>
        {{ $post->location }}
    </p>
@endif