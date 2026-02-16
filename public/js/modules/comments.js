// public/js/modules/comments.js

const COMMENT_EMOJIS  = {
    like: '👍',
    love: '❤️',
    haha: '😂',
    wow: '😮',
    sad: '😢',
    angry: '😡'
};

function createCommentHTML(comment) {
    const deleteBtn = comment.is_owner
        ? `<button onclick="deleteComment(${comment.id})" class="text-gray-400 hover:text-red-500 text-xs">Delete</button>`
        : '';

    const userReaction = comment.user_reaction;
    const reactionCounts = comment.reaction_counts || {};
    const totalReactions = Object.values(reactionCounts).reduce((a, b) => a + b, 0);

    const topReactions = Object.entries(reactionCounts)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 3)
        .map(([type]) => COMMENT_EMOJIS[type])
        .join('');

    return `
        <div class="flex gap-3 comment-item" id="comment-${comment.id}">
            <img src="${comment.user.avatar_url}" alt="${comment.user.name}" class="w-8 h-8 rounded-full object-cover">
            <div class="flex-1">
                <div class="bg-gray-100 rounded-2xl px-4 py-2">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-sm text-gray-800">${comment.user.name}</span>
                        ${deleteBtn}
                    </div>
                    <p class="text-gray-700 text-sm">${comment.content}</p>
                </div>

                <div class="flex items-center gap-4 mt-1 ml-2">
                    <span class="text-xs text-gray-500">${comment.created_at}</span>

                    ${totalReactions > 0 ? `
                        <div class="flex items-center gap-1 text-xs">
                            <span>${topReactions}</span>
                            <span class="text-gray-500">${totalReactions}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

/* =========================
   Toggle Comments Section
========================= */
document.addEventListener('click', function (e) {
    const toggleBtn = e.target.closest('.comment-toggle-btn');
    if (!toggleBtn) return;

    const postId = toggleBtn.dataset.postId;
    if (!postId) return;

    const commentsSection = document.getElementById(`comments-${postId}`);
    const commentsList = document.getElementById(`comments-list-${postId}`);

    if (!commentsSection || !commentsList) {
        console.warn('Comments section not found for post:', postId);
        return;
    }

    commentsSection.classList.toggle('hidden');

    if (
        !commentsSection.classList.contains('hidden') &&
        !commentsList.dataset.loaded
    ) {
        commentsList.innerHTML =
            '<p class="text-gray-400 text-sm text-center py-2">Loading...</p>';

        loadComments(postId, commentsList)
            .then(() => {
                commentsList.dataset.loaded = 'true';
            })
            .catch(() => {
                delete commentsList.dataset.loaded;
            });
    }
});

/* =========================
   Load Comments
========================= */
async function loadComments(postId, container) {
    try {
        const response = await fetch(`/posts/${postId}/comments`, {
            headers: { Accept: 'application/json' }
        });

        const data = await response.json();

        if (data.success) {
            if (data.comments.length) {
                container.innerHTML = data.comments
                    .map(comment => createCommentHTML(comment))
                    .join('');
            } else {
                container.innerHTML =
                    '<p class="text-gray-500 text-sm text-center py-2">No comments yet.</p>';
            }
        } else {
            throw new Error('Failed to load comments');
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        container.innerHTML =
            '<p class="text-red-500 text-sm text-center py-2">Error loading comments</p>';
    }
}

/* =========================
   Submit Comment
========================= */
document.addEventListener('submit', async function (e) {
    const form = e.target.closest('.comment-form');
    if (!form) return;

    e.preventDefault();

    const postId = form.dataset.postId;
    const input = form.querySelector('input[name="content"]');
    const content = input.value.trim();

    if (!content) return;

    try {
        const response = await fetch(`/posts/${postId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content')
            },
            body: JSON.stringify({ content })
        });

        const data = await response.json();

        if (data.success) {
            input.value = '';

            const commentsList = document.getElementById(`comments-list-${postId}`);
            commentsList.insertAdjacentHTML(
                'afterbegin',
                createCommentHTML(data.comment)
            );

            const postCard = document.querySelector(`[data-post-id="${postId}"]`);
            const countSpan = postCard?.querySelector('.comment-count');
            if (countSpan) {
                countSpan.textContent = parseInt(countSpan.textContent || 0) + 1;
            }
        }
    } catch (error) {
        console.error('Error posting comment:', error);
    }
});

/* =========================
   Delete Comment
========================= */
async function deleteComment(commentId) {
    if (!confirm('Delete this comment?')) return;

    try {
        const response = await fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content')
            }
        });

        if (response.ok) {
            const commentEl = document.getElementById(`comment-${commentId}`);
            const postCard = commentEl?.closest('[data-post-id]');
            const countSpan = postCard?.querySelector('.comment-count');

            if (countSpan) {
                countSpan.textContent = parseInt(countSpan.textContent || 1) - 1;
            }

            commentEl?.remove();
        }
    } catch (error) {
        console.error('Error deleting comment:', error);
    }
}
