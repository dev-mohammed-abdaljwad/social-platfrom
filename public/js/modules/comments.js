/* =========================
   Comment Reactions Config
========================= */
const COMMENT_EMOJIS = {
    like: '👍',
    love: '❤️',
    haha: '😂',
    wow: '😮',
    sad: '😢',
    angry: '😡'
};

/* =========================
   Create Comment HTML
========================= */
function createCommentHTML(comment) {

    const deleteBtn = comment.is_owner
        ? `<button onclick="deleteComment(${comment.id})"
                class="text-gray-400 hover:text-red-500 text-xs">Delete</button>`
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
        <div class="flex gap-3 comment-item" id="comment-${comment.id}" data-comment-id="${comment.id}">
            <img src="${comment.user.avatar_url}"
                 alt="${comment.user.name}"
                 class="w-8 h-8 rounded-full object-cover">

            <div class="flex-1 w-full">

                <div class="bg-gray-100 rounded-2xl px-4 py-2">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-sm text-gray-800">
                            ${comment.user.name}
                        </span>
                        ${deleteBtn}
                    </div>

                    <p class="text-gray-700 text-sm">
                        ${comment.content}
                    </p>
                </div>

                <!-- Comment Actions -->
                <div class="flex items-center gap-4 mt-1 ml-2">

                    <span class="text-xs text-gray-500">
                        ${comment.created_at}
                    </span>

                    <!-- Reaction Button -->
                    <div class="relative group">
                        <button
                            class="comment-react-btn text-xs ${
                                userReaction ? 'text-blue-600 font-semibold' : 'text-gray-500'
                            }"
                            data-comment-id="${comment.id}">
                            ${
                                userReaction
                                    ? `${COMMENT_EMOJIS[userReaction]} ${userReaction}`
                                    : 'Like'
                            }
                        </button>

                        <!-- Reaction Picker -->
                        <div class="hidden comment-reaction-picker absolute bottom-full mb-2
            bg-white shadow rounded-full p-2 flex items-center gap-1 z-20" >
                            ${Object.keys(COMMENT_EMOJIS).map(type => `
                                <button
                                    class="comment-reaction-option hover:scale-125 transition"
                                    data-comment-id="${comment.id}"
                                    data-type="${type}">
                                    ${COMMENT_EMOJIS[type]}
                                </button>
                            `).join('')}
                        </div>
                    </div>

                    <!-- Reply Button -->
                    ${!comment.parent_id ? `
                    <button class="text-xs text-gray-500 hover:text-gray-700 comment-reply-btn" data-comment-id="${comment.id}" data-user-name="${comment.user.name}">
                        Reply
                    </button>
                    ` : ''}

                    ${
                        totalReactions > 0
                            ? `
                            <div class="flex items-center gap-1 text-xs">
                                <span>${topReactions}</span>
                                <span class="text-gray-500">${totalReactions}</span>
                            </div>
                            `
                            : ''
                    }
                </div>
                
                <!-- Replies Section -->
                ${!comment.parent_id ? `
                <div class="replies-section mt-2 ml-4 hidden" id="replies-${comment.id}">
                    <div class="replies-list space-y-3" id="replies-list-${comment.id}">
                        ${comment.replies && comment.replies.length > 0 ? comment.replies.map(r => createCommentHTML(r)).join('') : ''}
                    </div>
                </div>
                <!-- View Replies Toggle -->
                ${(comment.replies_count > 0 || (comment.replies && comment.replies.length > 0)) ? `
                <button class="text-xs text-gray-500 hover:text-gray-700 font-medium mt-1 ml-2 toggle-replies-btn" data-comment-id="${comment.id}">
                    Show Replies (${comment.replies_count || comment.replies.length})
                </button>
                ` : ''}
                ` : ''}
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
    const commentsSection = document.getElementById(`comments-${postId}`);
    const commentsList = document.getElementById(`comments-list-${postId}`);

    if (!commentsSection || !commentsList) return;

    commentsSection.classList.toggle('hidden');

    if (!commentsSection.classList.contains('hidden') &&
        !commentsList.dataset.loaded) {

        commentsList.innerHTML =
            '<p class="text-gray-400 text-sm text-center py-2">Loading...</p>';

        loadComments(postId, commentsList)
            .then(() => commentsList.dataset.loaded = 'true')
            .catch(() => delete commentsList.dataset.loaded);
    }
});

/* =========================
   Load Comments
========================= */
async function loadComments(postId, container) {
    const response = await fetch(`/posts/${postId}/comments`, {
        headers: { Accept: 'application/json' }
    });

    const data = await response.json();

    if (!data.success) {
        container.innerHTML =
            '<p class="text-red-500 text-sm text-center py-2">Error loading comments</p>';
        return;
    }

    container.innerHTML = data.comments.length
        ? data.comments.map(createCommentHTML).join('')
        : '<p class="text-gray-500 text-sm text-center py-2">No comments yet.</p>';
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
    // Handle hidden input for parent_id
    let parentIdInput = form.querySelector('input[name="parent_id"]');
    const parentId = parentIdInput ? parentIdInput.value : null;

    if (!content) return;

    const body = { content };
    if (parentId) {
        body.parent_id = parentId;
    }

    const response = await fetch(`/posts/${postId}/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(body)
    });

    const data = await response.json();

    if (data.success) {
        input.value = '';
        if (parentIdInput) {
            parentIdInput.value = '';
            input.placeholder = 'Write a comment...';
        }
        
        let replyToBadge = form.querySelector('.reply-to-badge');
        if (replyToBadge) {
            replyToBadge.remove();
        }

        if (parentId) {
            // Append reply
            const replyList = document.getElementById(`replies-list-${parentId}`);
            if (replyList) {
                replyList.insertAdjacentHTML('beforeend', createCommentHTML(data.comment));
                
                // Show replies section if it was hidden
                const repliesSection = document.getElementById(`replies-${parentId}`);
                if (repliesSection) {
                    repliesSection.classList.remove('hidden');
                }
            }
        } else {
            // Append root comment
            const list = document.getElementById(`comments-list-${postId}`);
            if (list) {
                list.insertAdjacentHTML('afterbegin', createCommentHTML(data.comment));
            }
        }
    }
});

/* =========================
   Delete Comment
========================= */
async function deleteComment(commentId) {

    if (!confirm('Delete this comment?')) return;

    const response = await fetch(`/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]').content
        }
    });

    if (response.ok) {
        document.getElementById(`comment-${commentId}`)?.remove();
    }
}

/* =========================
   Toggle Comment Reaction
========================= */
document.addEventListener('click', async function (e) {

    const btn = e.target.closest('.comment-reaction-option');
    if (!btn) return;

    const commentId = btn.dataset.commentId;
    const type = btn.dataset.type;

    const response = await fetch(`/comments/${commentId}/reaction`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ type })
    });

    const data = await response.json();
    if (!data.success) return;

    const commentEl = document.getElementById(`comment-${commentId}`);
    const reactBtn = commentEl.querySelector('.comment-react-btn');

    if (data.user_reaction) {
        reactBtn.innerHTML =
            `${COMMENT_EMOJIS[data.user_reaction]} ${data.user_reaction}`;
        reactBtn.classList.add('text-blue-600', 'font-semibold');
    } else {
        reactBtn.innerHTML = 'Like';
        reactBtn.classList.remove('text-blue-600', 'font-semibold');
    }
});

/* =========================
   Toggle Comment Reaction Picker
========================= */
document.addEventListener('click', function (e) {

    // Open picker
    const reactBtn = e.target.closest('.comment-react-btn');
    if (reactBtn) {

        const commentId = reactBtn.dataset.commentId;
        const commentEl = document.getElementById(`comment-${commentId}`);
        const picker = commentEl.querySelector('.comment-reaction-picker');

        // اقفل كل الباقي
        document.querySelectorAll('.comment-reaction-picker')
            .forEach(p => p.classList.add('hidden'));

        picker.classList.toggle('hidden');
        return;
    }

    // لو ضغط برا reaction container
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('.comment-reaction-picker')
            .forEach(p => p.classList.add('hidden'));
    }

});

/* =========================
   Reply to Comment
========================= */
document.addEventListener('click', function (e) {
    const replyBtn = e.target.closest('.comment-reply-btn');
    if (!replyBtn) return;

    const commentId = replyBtn.dataset.commentId;
    const userName = replyBtn.dataset.userName;
    
    // Find the closest comment form (it's in the same post container)
    const postContainer = replyBtn.closest('.comments-section');
    if (!postContainer) return;
    
    const form = postContainer.querySelector('.comment-form');
    if (!form) return;

    const parentIdInput = form.querySelector('input[name="parent_id"]');
    const contentInput = form.querySelector('input[name="content"]');
    
    if (parentIdInput && contentInput) {
        parentIdInput.value = commentId;
        contentInput.placeholder = `Replying to ${userName}...`;
        contentInput.focus();
        
        // Add a visual indicator that we are replying
        let replyBadge = form.querySelector('.reply-to-badge');
        if (!replyBadge) {
            replyBadge = document.createElement('div');
            replyBadge.className = 'reply-to-badge absolute -top-6 left-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full flex items-center gap-1 z-10';
            replyBadge.innerHTML = `
                <span>Replying to <strong>${userName}</strong></span>
                <button type="button" class="cancel-reply hover:text-red-500 ml-1 rounded-full p-0.5" title="Cancel reply">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            `;
            form.querySelector('.input-wrapper').parentElement.appendChild(replyBadge);
        } else {
            replyBadge.querySelector('strong').textContent = userName;
        }
    }
});

/* =========================
   Cancel Reply
========================= */
document.addEventListener('click', function (e) {
    const cancelBtn = e.target.closest('.cancel-reply');
    if (!cancelBtn) return;

    const form = cancelBtn.closest('.comment-form');
    if (!form) return;

    const parentIdInput = form.querySelector('input[name="parent_id"]');
    const contentInput = form.querySelector('input[name="content"]');
    
    if (parentIdInput && contentInput) {
        parentIdInput.value = '';
        contentInput.placeholder = 'Write a comment...';
        
        const replyBadge = form.querySelector('.reply-to-badge');
        if (replyBadge) {
            replyBadge.remove();
        }
    }
});

/* =========================
   Toggle Replies View
========================= */
document.addEventListener('click', function(e) {
    const toggleBtn = e.target.closest('.toggle-replies-btn');
    if (!toggleBtn) return;
    
    const commentId = toggleBtn.dataset.commentId;
    const repliesSection = document.getElementById(`replies-${commentId}`);
    
    if (repliesSection) {
        if (repliesSection.classList.contains('hidden')) {
            repliesSection.classList.remove('hidden');
            const toggleText = toggleBtn.textContent;
            toggleBtn.dataset.originalText = toggleText;
            toggleBtn.textContent = 'Hide Replies';
        } else {
            repliesSection.classList.add('hidden');
            if (toggleBtn.dataset.originalText) {
                toggleBtn.textContent = toggleBtn.dataset.originalText;
            } else {
                toggleBtn.textContent = 'Show Replies';
            }
        }
    }
});
