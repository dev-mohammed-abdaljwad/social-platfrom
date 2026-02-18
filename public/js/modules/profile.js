
document.addEventListener("DOMContentLoaded", function () {
document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
                b.classList.add('text-gray-500');
            });
            
            this.classList.remove('text-gray-500');
            this.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
            
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            
            const tabId = this.dataset.tab + 'Tab';
            document.getElementById(tabId).classList.remove('hidden');
        });
    });
    /*
    |--------------------------------------------------------------------------
    | Toggle Comments Section
    |--------------------------------------------------------------------------
    */
    document.addEventListener("click", function (e) {
        const toggleBtn = e.target.closest(".toggle-comments");
        if (!toggleBtn) return;

        const postId = toggleBtn.dataset.postId;
        const commentsContainer = document.getElementById(`comments-${postId}`);

        if (!commentsContainer) return;

        commentsContainer.classList.toggle("hidden");

        if (!commentsContainer.dataset.loaded) {
            loadComments(postId);
            commentsContainer.dataset.loaded = true;
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Load Comments
    |--------------------------------------------------------------------------
    */
    async function loadComments(postId) {
        try {
            const response = await fetch(`/posts/${postId}/comments`);
            const data = await response.json();

            const container = document.getElementById(`comments-${postId}`);
            if (!container) return;

            let html = "";

            data.comments.forEach(comment => {

                const userReaction = comment.user_reaction;
                const reactionEmoji = userReaction ? userReaction.emoji : "👍";
                const reactionLabel = userReaction ? userReaction.type : "Like";

                html += `
                <div class="flex gap-3 comment-item mb-3" id="comment-${comment.id}">
                    
                    <a href="/profile/${comment.user.id}">
                        <img src="${comment.user.avatar_url || '/images/default-avatar.png'}"
                             class="w-8 h-8 rounded-full object-cover">
                    </a>

                    <div class="flex-1">
                        <div class="bg-gray-100 rounded-2xl px-4 py-2">
                            <a href="/profile/${comment.user.id}" 
                               class="font-semibold text-gray-800 text-sm">
                                ${comment.user.name}
                            </a>
                            <p class="text-gray-700 text-sm">${comment.content}</p>
                        </div>

                        <div class="flex items-center gap-4 mt-1 ml-4">
                            <span class="text-xs text-gray-400">
                                ${comment.created_at}
                            </span>

                            <button 
                                class="comment-reaction-btn flex items-center gap-1 text-xs text-gray-600 hover:text-blue-600"
                                data-comment-id="${comment.id}"
                            >
                                <span class="reaction-emoji">${reactionEmoji}</span>
                                <span>${reactionLabel}</span>
                            </button>

                            <span class="text-xs text-gray-500 reaction-count">
                                ${comment.reactions_count || 0}
                            </span>
                        </div>
                    </div>
                </div>
                `;
            });

            container.innerHTML = html;

        } catch (error) {
            console.error("Error loading comments:", error);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Add Comment
    |--------------------------------------------------------------------------
    */
    document.addEventListener("submit", async function (e) {
        if (!e.target.matches(".add-comment-form")) return;

        e.preventDefault();

        const form = e.target;
        const postId = form.dataset.postId;
        const input = form.querySelector("input[name='content']");
        const content = input.value.trim();

        if (!content) return;

        try {
            const response = await fetch(`/posts/${postId}/comments`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content })
            });

            const data = await response.json();

            if (data.comment) {
                input.value = "";
                loadComments(postId);
            }

        } catch (error) {
            console.error("Error adding comment:", error);
        }
    });

});
