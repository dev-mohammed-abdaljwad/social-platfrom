// public/js/modules/reactions.js
const EMOJIS = {
    'like': '👍', 
    'love': '❤️', 
    'haha': '😂', 
    'wow': '😮', 
    'sad': '😢', 
    'angry': '😡'
};

// ==========================================
// POST REACTIONS
// ==========================================

// Close picker when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.reaction-picker-container')) {
        document.querySelectorAll('.reaction-picker').forEach(p => p.classList.add('hidden'));
    }
});

// Toggle picker on main button click
document.addEventListener('click', function(e) {
    const mainBtn = e.target.closest('.reaction-btn-main');
    if (mainBtn) {
        const postId = mainBtn.dataset.postId;
        const picker = document.getElementById(`picker-${postId}`);
        
        // Close all other pickers
        document.querySelectorAll('.reaction-picker').forEach(p => {
            if (p.id !== `picker-${postId}`) {
                p.classList.add('hidden');
            }
        });
        
        // Toggle current picker
        if (picker) {
            picker.classList.toggle('hidden');
        }
        e.stopPropagation();
    }
});

// Handle post reaction option click
document.addEventListener('click', async function(e) {
    const option = e.target.closest('.reaction-option');
    if (option) {
        const postId = option.dataset.postId;
        const chosenType = option.dataset.type;
        const postCard = document.querySelector(`[data-post-id="${postId}"]`);
        const mainBtn = postCard.querySelector('.reaction-btn-main');
        const picker = document.getElementById(`picker-${postId}`);
        
        // Get current reaction type
        const reactionLabel = mainBtn.querySelector('.reaction-label');
        const currentType = reactionLabel ? reactionLabel.textContent.trim().toLowerCase() : null;
        
        // Check if unreacting
        const isUnreacting = currentType === chosenType;
        
        // Close picker immediately
        if (picker) picker.classList.add('hidden');
        
        // Optimistic UI update
        if (isUnreacting) {
            mainBtn.innerHTML = `
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.708C19.746 10 20.5 10.811 20.5 11.812c0 .387-.121.76-.346 1.07l-2.44 3.356c-.311.43-.802.682-1.324.682H8.5V10c0-.828.672-1.5 1.5-1.5h.5V4.667c0-.92.747-1.667 1.667-1.667h1.666c.92 0 1.667.747 1.667 1.667V10zM8.5 10H5.5c-.828 0-1.5.672-1.5 1.5v5c0 .828.672 1.5 1.5 1.5h3V10z"></path>
                </svg>
                <span class="text-xs sm:text-sm">Like</span>
            `;
            mainBtn.classList.remove('text-blue-600', 'font-semibold');
        } else {
            const emoji = EMOJIS[chosenType];
            mainBtn.innerHTML = `
                <span class="text-lg reaction-emoji">${emoji}</span>
                <span class="text-xs sm:text-sm capitalize reaction-label">${chosenType}</span>
            `;
            mainBtn.classList.add('text-blue-600', 'font-semibold');
        }
        
        // Send AJAX request
        try {
            const response = await fetch(`/posts/${postId}/react`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ type: chosenType })
            });

            if (response.ok) {
                const data = await response.json();
                updatePostReactionUI(postId, data);
            }
        } catch (error) {
            console.error('Error reacting to post:', error);
        }
        e.stopPropagation();
    }
});

function updatePostReactionUI(postId, data) {
    const postCard = document.querySelector(`[data-post-id="${postId}"]`);
    if (!postCard) return;

    const mainBtn = postCard.querySelector('.reaction-btn-main');
    const countSpan = postCard.querySelector('.reactions-count');
    const iconsDiv = postCard.querySelector('.view-reactions-btn div');

    // Update main button
    if (data.user_reaction) {
        const emoji = EMOJIS[data.user_reaction];
        mainBtn.innerHTML = `
            <span class="text-lg reaction-emoji">${emoji}</span>
            <span class="text-xs sm:text-sm capitalize reaction-label">${data.user_reaction}</span>
        `;
        mainBtn.classList.add('text-blue-600', 'font-semibold');
    } else {
        mainBtn.innerHTML = `
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.708C19.746 10 20.5 10.811 20.5 11.812c0 .387-.121.76-.346 1.07l-2.44 3.356c-.311.43-.802.682-1.324.682H8.5V10c0-.828.672-1.5 1.5-1.5h.5V4.667c0-.92.747-1.667 1.667-1.667h1.666c.92 0 1.667.747 1.667 1.667V10zM8.5 10H5.5c-.828 0-1.5.672-1.5 1.5v5c0 .828.672 1.5 1.5 1.5h3V10z"></path>
            </svg>
            <span class="text-xs sm:text-sm">Like</span>
        `;
        mainBtn.classList.remove('text-blue-600', 'font-semibold');
    }

    // Update counts and icons
    countSpan.textContent = data.counts.total > 0 ? data.counts.total : '';
    
    const topReactions = Object.entries(data.counts.detailed || {})
        .sort((a, b) => b[1] - a[1])
        .slice(0, 3);
    
    iconsDiv.innerHTML = topReactions
        .map(([type]) => `<span class="text-xs">${EMOJIS[type] || '❓'}</span>`)
        .join('');
}

// ==========================================
// COMMENT REACTIONS
// ==========================================

// Close comment picker when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.comment-reaction-container')) {
        document.querySelectorAll('.comment-reaction-picker').forEach(p => {
            p.classList.add('hidden');
        });
    }
});

// Toggle comment picker on main button click
document.addEventListener('click', function(e) {
    const mainBtn = e.target.closest('.comment-reaction-btn-main');
    if (mainBtn) {
        const commentId = mainBtn.dataset.commentId;
        const picker = document.getElementById(`comment-picker-${commentId}`);
        
        // Close all other pickers
        document.querySelectorAll('.comment-reaction-picker').forEach(p => {
            if (p.id !== `comment-picker-${commentId}`) {
                p.classList.add('hidden');
            }
        });
        
        // Toggle current picker
        if (picker) picker.classList.toggle('hidden');
        e.stopPropagation();
    }
});

// Handle comment reaction option click
document.addEventListener('click', async function(e) {
    const option = e.target.closest('.comment-reaction-option');
    if (option) {
        const commentId = option.dataset.commentId;
        const chosenType = option.dataset.type;
        const picker = document.getElementById(`comment-picker-${commentId}`);
        
        // Close picker
        if (picker) picker.classList.add('hidden');
        
        const commentEl = document.getElementById(`comment-${commentId}`);
        const mainBtn = commentEl.querySelector('.comment-reaction-btn-main');
        
        // Get current reaction
        const currentReactionSpan = mainBtn.querySelector('span:last-child');
        const currentType = currentReactionSpan?.textContent.toLowerCase() || 'like';
        
        const isUnreacting = currentType === chosenType;
        
        // Optimistic update
        if (isUnreacting) {
            mainBtn.innerHTML = `<span>👍</span><span>Like</span>`;
            mainBtn.classList.remove('text-blue-600');
            mainBtn.classList.add('text-gray-400');
        } else {
            mainBtn.innerHTML = `<span>${EMOJIS[chosenType]}</span><span class="capitalize">${chosenType}</span>`;
            mainBtn.classList.remove('text-gray-400');
            mainBtn.classList.add('text-blue-600');
        }
        
        // AJAX
        try {
            const response = await fetch(`/comments/${commentId}/react`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ type: chosenType })
            });

            if (response.ok) {
                const data = await response.json();
                updateCommentReactionUI(commentId, data);
            }
        } catch (error) {
            console.error('Error reacting to comment:', error);
        }
        e.stopPropagation();
    }
});

function updateCommentReactionUI(commentId, data) {
    const commentEl = document.getElementById(`comment-${commentId}`);
    if (!commentEl) return;

    const mainBtn = commentEl.querySelector('.comment-reaction-btn-main');

    // Update main button
    if (data.user_reaction) {
        const emoji = EMOJIS[data.user_reaction];
        mainBtn.innerHTML = `<span>${emoji}</span><span class="capitalize">${data.user_reaction}</span>`;
        mainBtn.classList.remove('text-gray-400');
        mainBtn.classList.add('text-blue-600');
    } else {
        mainBtn.innerHTML = `<span>👍</span><span>Like</span>`;
        mainBtn.classList.remove('text-blue-600');
        mainBtn.classList.add('text-gray-400');
    }

    // Update reaction display
    const topReactions = Object.entries(data.counts.detailed || {})
        .sort((a, b) => b[1] - a[1])
        .slice(0, 3)
        .map(([type]) => EMOJIS[type])
        .join('');

    const displayDiv = commentEl.querySelector('.comment-reactions-display');
    if (data.counts.total > 0) {
        if (!displayDiv) {
            const container = commentEl.querySelector('.comment-reaction-container');
            container.insertAdjacentHTML('afterend', `
                <div class="comment-reactions-display flex items-center gap-1 text-xs">
                    <span class="comment-reaction-icons">${topReactions}</span>
                    <span class="comment-reactions-count text-gray-500">${data.counts.total}</span>
                </div>
            `);
        } else {
            displayDiv.querySelector('.comment-reaction-icons').textContent = topReactions;
            displayDiv.querySelector('.comment-reactions-count').textContent = data.counts.total;
        }
    } else if (displayDiv) {
        displayDiv.remove();
    }
}