@extends('layouts.app')

@section('title', 'Saved Posts - SocialTime')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Saved Posts</h1>
            <span class="text-gray-500">{{ $savedPosts->count() }} {{ Str::plural('post', $savedPosts->count()) }}</span>
        </div>

        <!-- Saved Posts -->
        <div class="space-y-6">
            @forelse($savedPosts as $post)
                @include('components.post-card', ['post' => $post])
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    <p class="text-gray-500 text-lg">No saved posts yet</p>
                    <p class="text-gray-400 mt-1">Save posts to view them later</p>
                    <a href="{{ route('home') }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Browse Posts
                    </a>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
     <script src="js/modules/posts.js"></script>
    <script src="js/modules/reactions.js"></script>
    <script src="js/modules/comments.js"></script>
  
@endpush
