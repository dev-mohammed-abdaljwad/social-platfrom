@php
    $icons = [
        'edit' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'delete' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
    ];
    $labels = ['edit' => 'Edit Post', 'delete' => 'Delete Post'];
    $colors = ['edit' => 'text-gray-700', 'delete' => 'text-red-600'];
@endphp

<button class="w-full px-4 py-2 text-left hover:bg-gray-100 rounded-t-lg {{ $colors[$type] }} {{ $type === 'delete' ? 'rounded-b-lg' : '' }} {{ $type }}-post-btn" 
        data-post-id="{{ $post->id }}"
        @if($type === 'edit')
            data-content="{{ $post->content }}"
            data-location="{{ $post->location }}"
            data-privacy="{{ $post->privacy?->value }}"
        @endif>
    <span class="flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$type] }}"></path>
        </svg>
        {{ $labels[$type] }}
    </span>
</button>
