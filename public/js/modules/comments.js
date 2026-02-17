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
        <div class="flex gap-3 comment-item" id="comment-${comment.id}">
            <img src="${comment.user.avatar_url}"
                 alt="${comment.user.name}"
                 class="w-8 h-8 rounded-full object-cover">

            <div class="flex-1">

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

    if (!content) return;

    const response = await fetch(`/posts/${postId}/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ content })
    });

    const data = await response.json();

    if (data.success) {
        input.value = '';
        const list = document.getElementById(`comments-list-${postId}`);
        list.insertAdjacentHTML('afterbegin', createCommentHTML(data.comment));
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
