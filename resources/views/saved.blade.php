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
                @include('partials.post-card', ['post' => $post])
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
<script>
    // Like functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const postId = this.dataset.postId;
            try {
                const response = await fetch(`/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                if (data.success) {
                    const icon = this.querySelector('svg');
                    if (data.liked) {
                        this.classList.add('text-red-500');
                        icon.setAttribute('fill', 'currentColor');
                    } else {
                        this.classList.remove('text-red-500');
                        icon.setAttribute('fill', 'none');
                    }
                    this.querySelector('span').textContent = data.likes_count;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });

    // Save functionality
    document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const postId = this.dataset.postId;
            try {
                const response = await fetch(`/posts/${postId}/save`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                if (data.success) {
                    const icon = this.querySelector('svg');
                    if (data.saved) {
                        this.classList.add('text-yellow-500');
                        icon.setAttribute('fill', 'currentColor');
                    } else {
                        this.classList.remove('text-yellow-500');
                        icon.setAttribute('fill', 'none');
                        // Remove the post card from the page
                        this.closest('[data-post-id]').remove();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });

    // Comment toggle
    document.querySelectorAll('.comment-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const commentsSection = document.getElementById(`comments-${postId}`);
            commentsSection.classList.toggle('hidden');
            
            if (!commentsSection.classList.contains('hidden')) {
                loadComments(postId);
            }
        });
    });

    async function loadComments(postId) {
        const commentsList = document.getElementById(`comments-list-${postId}`);
        
        try {
            const response = await fetch(`/posts/${postId}/comments`);
            const data = await response.json();
            
            if (data.comments.length === 0) {
                commentsList.innerHTML = '<p class="text-gray-400 text-sm text-center py-2">No comments yet. Be the first!</p>';
                return;
            }
            
            commentsList.innerHTML = data.comments.map(comment => renderComment(comment)).join('');
        } catch (error) {
            console.error('Error loading comments:', error);
            commentsList.innerHTML = '<p class="text-red-500 text-sm">Failed to load comments</p>';
        }
    }

    function renderComment(comment) {
        return `
            <div class="flex gap-3">
                <img src="${comment.user.avatar_url}" alt="${comment.user.name}" class="w-8 h-8 rounded-full object-cover">
                <div class="flex-1">
                    <div class="bg-gray-100 rounded-lg px-3 py-2">
                        <span class="font-semibold text-sm">${comment.user.name}</span>
                        <p class="text-sm text-gray-700">${comment.content}</p>
                    </div>
                    <span class="text-xs text-gray-400 ml-2">${comment.created_at_human}</span>
                </div>
            </div>
        `;
    }

    // Comment form submit
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const postId = this.dataset.postId;
            const input = this.querySelector('input[name="content"]');
            const content = input.value.trim();
            
            if (!content) return;
            
            try {
                const response = await fetch(`/posts/${postId}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ content })
                });
                const data = await response.json();
                
                if (data.success) {
                    input.value = '';
                    loadComments(postId);
                    
                    // Update comment count
                    const countSpan = document.querySelector(`.comment-toggle-btn[data-post-id="${postId}"] .comment-count`);
                    if (countSpan) {
                        countSpan.textContent = parseInt(countSpan.textContent) + 1;
                    }
                }
            } catch (error) {
                console.error('Error posting comment:', error);
            }
        });
    });
</script>
@endpush
