
    async function uploadProfilePicture(input) {
        if (!input.files || !input.files[0]) return;
        
        const file = input.files[0];
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('Profile picture must be less than 5MB');
            return;
        }
        
        const formData = new FormData();
        formData.append('profile_picture', file);
        
        try {
            const response = await fetch('/profile/picture', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });
            
            if (response.ok) {
                const data = await response.json();
                document.getElementById('profilePictureImage').src = data.profile_picture_url;
                // Update navbar avatar if exists
                const navAvatar = document.querySelector('nav img[alt="{{ auth()->user()->name ?? "" }}"]');
                if (navAvatar) navAvatar.src = data.profile_picture_url;
            } else {
                const error = await response.json();
                alert(error.message || 'Failed to upload profile picture');
            }
        } catch (error) {
            console.error('Error uploading profile picture:', error);
            alert('Failed to upload profile picture');
        }
        
        input.value = '';
    }
    
    // Cover Photo Upload
    async function uploadCoverPhoto(input) {
        if (!input.files || !input.files[0]) return;
        
        const file = input.files[0];
        
        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert('Cover photo must be less than 10MB');
            return;
        }
        
        const formData = new FormData();
        formData.append('cover_photo', file);
        
        try {
            const response = await fetch('/profile/cover', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });
            
            if (response.ok) {
                const data = await response.json();
                // Reload the page to show the new cover photo
                location.reload();
            } else {
                const error = await response.json();
                alert(error.message || 'Failed to upload cover photo');
            }
        } catch (error) {
            console.error('Error uploading cover photo:', error);
            alert('Failed to upload cover photo');
        }
        
        input.value = '';
    }
    
    // Remove Cover Photo
    async function removeCoverPhoto() {
        if (!confirm('Are you sure you want to remove your cover photo?')) return;
        
        try {
            const response = await fetch('/profile/cover', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                location.reload();
            } else {
                const error = await response.json();
                alert(error.message || 'Failed to remove cover photo');
            }
        } catch (error) {
            console.error('Error removing cover photo:', error);
            alert('Failed to remove cover photo');
        }
    }

    // Tab switching
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

    // Like button functionality with optimistic updates
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.setAttribute('data-listener', 'true');
        btn.addEventListener('click', async function() {
            @auth
            const postId = this.dataset.postId;
            const btn = this;
            const postCard = document.querySelector(`.bg-white[data-post-id="${postId}"]`);
            const countSpan = postCard.querySelector('.likes-count');
            const svg = btn.querySelector('svg');
            const isLiked = btn.classList.contains('text-red-500');
            const currentCount = parseInt(countSpan.textContent) || 0;
            
            // Optimistic update - instant UI change
            if (isLiked) {
                btn.classList.remove('text-red-500');
                svg.setAttribute('fill', 'none');
                countSpan.textContent = Math.max(0, currentCount - 1);
            } else {
                btn.classList.add('text-red-500');
                svg.setAttribute('fill', 'currentColor');
                countSpan.textContent = currentCount + 1;
            }
            
            try {
                const response = await fetch(`/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    countSpan.textContent = data.likes_count;
                } else {
                    // Revert on error
                    btn.classList.toggle('text-red-500');
                    svg.setAttribute('fill', isLiked ? 'currentColor' : 'none');
                    countSpan.textContent = currentCount;
                }
            } catch (error) {
                console.error('Error liking post:', error);
                // Revert on error
                btn.classList.toggle('text-red-500');
                svg.setAttribute('fill', isLiked ? 'currentColor' : 'none');
                countSpan.textContent = currentCount;
            }
            @else
            window.location.href = '{{ route("login") }}';
            @endauth
        });
    });

    // View likes modal functionality
    document.querySelectorAll('.view-likes-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            openLikesModal(postId);
        });
    });

    function openLikesModal(postId) {
        document.getElementById('likesModal').classList.remove('hidden');
        document.getElementById('likesModalContent').innerHTML = '<div class="text-center py-4 text-gray-500">Loading...</div>';
        
        fetch(`/posts/${postId}/likes`)
            .then(response => response.json())
            .then(data => {
                if (data.likes && data.likes.length > 0) {
                    let html = '<div class="space-y-3">';
                    data.likes.forEach(like => {
                        html += `
                            <a href="/profile/${like.user.id}" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                <img src="${like.user.avatar_url || '/images/default-avatar.png'}" 
                                     alt="${like.user.name}" 
                                     class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <p class="font-semibold text-gray-800">${like.user.name}</p>
                                    <p class="text-sm text-gray-500">@${like.user.username || like.user.name.toLowerCase().replace(/\\s+/g, '')}</p>
                                </div>
                            </a>
                        `;
                    });
                    html += '</div>';
                    document.getElementById('likesModalContent').innerHTML = html;
                } else {
                    document.getElementById('likesModalContent').innerHTML = '<div class="text-center py-4 text-gray-500">No likes yet</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching likes:', error);
                document.getElementById('likesModalContent').innerHTML = '<div class="text-center py-4 text-red-500">Error loading likes</div>';
            });
    }

    function closeLikesModal() {
        document.getElementById('likesModal').classList.add('hidden');
    }

    document.getElementById('likesModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLikesModal();
        }
    });

    // Comment toggle functionality
    document.querySelectorAll('.comment-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const commentsSection = document.getElementById(`comments-${postId}`);
            
            if (commentsSection.classList.contains('hidden')) {
                commentsSection.classList.remove('hidden');
                loadComments(postId);
            } else {
                commentsSection.classList.add('hidden');
            }
        });
    });

    // Load comments for a post
    async function loadComments(postId) {
        const commentsList = document.getElementById(`comments-list-${postId}`);
        
        try {
            const response = await fetch(`/posts/${postId}/comments`);
            const data = await response.json();
            
            if (data.comments && data.comments.length > 0) {
                let html = '';
                data.comments.forEach(comment => {
                    const likedClass = comment.is_liked ? 'text-red-500' : 'text-gray-400';
                    const heartFill = comment.is_liked ? 'fill-current' : '';
                    html += `
                        <div class="flex gap-3 comment-item" id="comment-${comment.id}">
                            <a href="/profile/${comment.user.id}">
                                <img src="${comment.user.avatar_url || '/images/default-avatar.png'}" 
                                     alt="${comment.user.name}" 
                                     class="w-8 h-8 rounded-full object-cover">
                            </a>
                            <div class="flex-1">
                                <div class="bg-gray-100 rounded-2xl px-4 py-2">
                                    <a href="/profile/${comment.user.id}" class="font-semibold text-gray-800 text-sm hover:underline">${comment.user.name}</a>
                                    <p class="text-gray-700 text-sm">${comment.content}</p>
                                </div>
                                <div class="flex items-center gap-4 mt-1 ml-4">
                                    <span class="text-xs text-gray-400">${comment.created_at}</span>
                                    <button onclick="likeComment(${comment.id})" class="comment-like-btn flex items-center gap-1 text-xs ${likedClass} hover:text-red-500 transition-colors" data-comment-id="${comment.id}">
                                        <svg class="w-4 h-4 ${heartFill}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span class="comment-likes-count">${comment.likes_count || 0}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                commentsList.innerHTML = html;
            } else {
                commentsList.innerHTML = '<p class="text-center text-gray-400 text-sm py-2">No comments yet. Be the first to comment!</p>';
            }
        } catch (error) {
            console.error('Error loading comments:', error);
            commentsList.innerHTML = '<p class="text-center text-red-500 text-sm py-2">Error loading comments</p>';
        }
    }

    // Like comment function with optimistic update
    async function likeComment(commentId) {
        @auth
        const commentEl = document.getElementById(`comment-${commentId}`);
        const likeBtn = commentEl.querySelector('.comment-like-btn');
        const countSpan = likeBtn.querySelector('.comment-likes-count');
        const svg = likeBtn.querySelector('svg');
        const isLiked = likeBtn.classList.contains('text-red-500');
        const currentCount = parseInt(countSpan.textContent) || 0;
        
        // Optimistic update
        if (isLiked) {
            likeBtn.classList.remove('text-red-500');
            likeBtn.classList.add('text-gray-400');
            svg.classList.remove('fill-current');
            countSpan.textContent = Math.max(0, currentCount - 1);
        } else {
            likeBtn.classList.remove('text-gray-400');
            likeBtn.classList.add('text-red-500');
            svg.classList.add('fill-current');
            countSpan.textContent = currentCount + 1;
        }
        
        try {
            const response = await fetch(`/comments/${commentId}/like`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                countSpan.textContent = data.likes_count;
            } else {
                // Revert on error
                likeBtn.classList.toggle('text-red-500');
                likeBtn.classList.toggle('text-gray-400');
                svg.classList.toggle('fill-current');
                countSpan.textContent = currentCount;
            }
        } catch (error) {
            console.error('Error liking comment:', error);
            // Revert on error
            likeBtn.classList.toggle('text-red-500');
            likeBtn.classList.toggle('text-gray-400');
            svg.classList.toggle('fill-current');
            countSpan.textContent = currentCount;
        }
        @else
        window.location.href = '{{ route("login") }}';
        @endauth
    }

    // Comment form submission
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
                
                if (response.ok) {
                    input.value = '';
                    loadComments(postId);
                    
                    // Update comment count
                    const postCard = document.querySelector(`[data-post-id="${postId}"]`);
                    const countSpan = postCard.querySelector('.comment-count');
                    if (countSpan) {
                        countSpan.textContent = parseInt(countSpan.textContent) + 1;
                    }
                }
            } catch (error) {
                console.error('Error posting comment:', error);
            }
        });
    });

    // Share modal state
    let currentSharePostId = null;
    let currentShareBtn = null;

    // Share button functionality - opens modal
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            @auth
            const postId = this.dataset.postId;
            currentSharePostId = postId;
            currentShareBtn = this;
            
            // Check if already shared - if so, unshare directly
            if (this.classList.contains('text-green-500')) {
                unsharePost(postId, this);
                return;
            }
            
            // Get post data for preview
            const postCard = document.querySelector(`[data-post-id="${postId}"]`);
            const userName = postCard.querySelector('a.font-semibold')?.textContent || 'User';
            const postContent = postCard.querySelector('.whitespace-pre-line')?.textContent || '';
            const postImage = postCard.querySelector('img[alt="Post image"]')?.src || '';
            
            // Build preview
            let previewHTML = `
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-semibold text-sm text-gray-800">${userName}</span>
                </div>
            `;
            if (postContent) {
                previewHTML += `<p class="text-gray-700 text-sm line-clamp-3">${postContent}</p>`;
            }
            if (postImage) {
                previewHTML += `<img src="${postImage}" class="mt-2 rounded max-h-32 object-cover" alt="Preview">`;
            }
            
            document.getElementById('sharePostPreview').innerHTML = previewHTML;
            document.getElementById('shareContent').value = '';
            document.getElementById('shareModal').classList.remove('hidden');
            @else
            openLoginModal();
            @endauth
        });
    });

    // Close share modal
    function closeShareModal() {
        document.getElementById('shareModal').classList.add('hidden');
        currentSharePostId = null;
        currentShareBtn = null;
    }

    // Confirm share with optional content
    async function confirmShare() {
        if (!currentSharePostId) return;
        
        const content = document.getElementById('shareContent').value.trim();
        
        try {
            const response = await fetch(`/posts/${currentSharePostId}/share`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content: content || null })
            });

            if (response.ok) {
                const data = await response.json();
                const countSpan = currentShareBtn.querySelector('.shares-count');
                const svg = currentShareBtn.querySelector('svg');
                
                countSpan.textContent = data.shares_count;
                
                if (data.shared) {
                    currentShareBtn.classList.add('text-green-500');
                    svg.setAttribute('fill', 'currentColor');
                }
                
                closeShareModal();
            }
        } catch (error) {
            console.error('Error sharing post:', error);
        }
    }

    // Unshare post (toggle off)
    async function unsharePost(postId, btn) {
        try {
            const response = await fetch(`/posts/${postId}/share`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                const data = await response.json();
                const countSpan = btn.querySelector('.shares-count');
                const svg = btn.querySelector('svg');
                
                countSpan.textContent = data.shares_count;
                
                if (!data.shared) {
                    btn.classList.remove('text-green-500');
                    svg.setAttribute('fill', 'none');
                }
            }
        } catch (error) {
            console.error('Error unsharing post:', error);
        }
    }

    // Post menu toggle
    document.querySelectorAll('.post-menu-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const postId = this.dataset.postId;
            const menu = document.querySelector(`.post-menu[data-post-id="${postId}"]`);
            
            // Close all other menus
            document.querySelectorAll('.post-menu, .share-menu').forEach(m => {
                if (m !== menu) m.classList.add('hidden');
            });
            
            menu.classList.toggle('hidden');
        });
    });

    // Share menu toggle
    document.querySelectorAll('.share-menu-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const shareId = this.dataset.shareId;
            const menu = document.querySelector(`.share-menu[data-share-id="${shareId}"]`);
            
            // Close all other menus
            document.querySelectorAll('.post-menu, .share-menu').forEach(m => {
                if (m !== menu) m.classList.add('hidden');
            });
            
            menu.classList.toggle('hidden');
        });
    });

    // Close menus when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.post-menu, .share-menu').forEach(m => m.classList.add('hidden'));
    });

    // Edit post button
    document.querySelectorAll('.edit-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const content = this.dataset.content || '';
            const privacy = this.dataset.privacy || 'public';
            
            document.getElementById('editPostId').value = postId;
            document.getElementById('editPostContent').value = content;
            document.getElementById('editPostPrivacy').value = privacy;
            document.getElementById('editPostModal').classList.remove('hidden');
            
            // Close menu
            document.querySelector(`.post-menu[data-post-id="${postId}"]`).classList.add('hidden');
        });
    });

    // Delete post button
    document.querySelectorAll('.delete-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            
            document.getElementById('deleteItemId').value = postId;
            document.getElementById('deleteItemType').value = 'post';
            document.getElementById('deleteConfirmTitle').textContent = 'Delete Post?';
            document.getElementById('deleteConfirmMessage').textContent = 'This action cannot be undone. Are you sure you want to delete this post?';
            document.getElementById('deleteConfirmModal').classList.remove('hidden');
            
            // Close menu
            document.querySelector(`.post-menu[data-post-id="${postId}"]`).classList.add('hidden');
        });
    });

    // Edit share button
    document.querySelectorAll('.edit-share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const shareId = this.dataset.shareId;
            const content = this.dataset.content || '';
            
            document.getElementById('editShareId').value = shareId;
            document.getElementById('editShareContent').value = content;
            document.getElementById('editShareModal').classList.remove('hidden');
            
            // Close menu
            document.querySelector(`.share-menu[data-share-id="${shareId}"]`).classList.add('hidden');
        });
    });

    // Delete share button
    document.querySelectorAll('.delete-share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const shareId = this.dataset.shareId;
            
            document.getElementById('deleteItemId').value = shareId;
            document.getElementById('deleteItemType').value = 'share';
            document.getElementById('deleteConfirmTitle').textContent = 'Delete Share?';
            document.getElementById('deleteConfirmMessage').textContent = 'This action cannot be undone. Are you sure you want to delete this share?';
            document.getElementById('deleteConfirmModal').classList.remove('hidden');
            
            // Close menu
            document.querySelector(`.share-menu[data-share-id="${shareId}"]`).classList.add('hidden');
        });
    });

    // Modal functions
    function closeEditPostModal() {
        document.getElementById('editPostModal').classList.add('hidden');
    }

    function closeEditShareModal() {
        document.getElementById('editShareModal').classList.add('hidden');
    }

    function closeDeleteConfirmModal() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    }

    // Save edit post
    async function saveEditPost() {
        const postId = document.getElementById('editPostId').value;
        const content = document.getElementById('editPostContent').value;
        const privacy = document.getElementById('editPostPrivacy').value;
        
        try {
            const response = await fetch(`/posts/${postId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content, privacy })
            });

            if (response.ok) {
                const data = await response.json();
                // Update post content in the DOM
                const postCard = document.querySelector(`[data-post-id="${postId}"]`);
                if (postCard) {
                    const contentEl = postCard.querySelector('.post-content');
                    if (contentEl) {
                        contentEl.textContent = content;
                    }
                    // Update the edit button data attribute
                    const editBtn = postCard.querySelector('.edit-post-btn');
                    if (editBtn) {
                        editBtn.dataset.content = content;
                        editBtn.dataset.privacy = privacy;
                    }
                }
                closeEditPostModal();
            } else {
                const error = await response.json();
                alert(error.message || 'Failed to update post');
            }
        } catch (error) {
            console.error('Error updating post:', error);
            alert('Failed to update post');
        }
    }

    // Save edit share
    async function saveEditShare() {
        const shareId = document.getElementById('editShareId').value;
        const content = document.getElementById('editShareContent').value;
        
        try {
            const response = await fetch(`/shares/${shareId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content })
            });

            if (response.ok) {
                const data = await response.json();
                // Update share content in the DOM
                const shareCard = document.querySelector(`[data-share-id="${shareId}"]`);
                if (shareCard) {
                    let contentEl = shareCard.querySelector('.share-content');
                    if (content) {
                        if (contentEl) {
                            contentEl.textContent = content;
                        } else {
                            // Create content element if it doesn't exist
                            const headerDiv = shareCard.querySelector('.border-b');
                            const newContentEl = document.createElement('p');
                            newContentEl.className = 'text-gray-700 mb-3 share-content';
                            newContentEl.textContent = content;
                            headerDiv.after(newContentEl);
                        }
                    } else if (contentEl) {
                        contentEl.remove();
                    }
                    // Update the edit button data attribute
                    const editBtn = shareCard.querySelector('.edit-share-btn');
                    if (editBtn) {
                        editBtn.dataset.content = content;
                    }
                }
                closeEditShareModal();
            } else {
                const error = await response.json();
                alert(error.message || 'Failed to update share');
            }
        } catch (error) {
            console.error('Error updating share:', error);
            alert('Failed to update share');
        }
    }

    // Confirm delete
    async function confirmDelete() {
        const itemId = document.getElementById('deleteItemId').value;
        const itemType = document.getElementById('deleteItemType').value;
        
        const url = itemType === 'post' ? `/posts/${itemId}` : `/shares/${itemId}`;
        
        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                // Remove the item from the DOM
                const selector = itemType === 'post' ? `[data-post-id="${itemId}"]` : `[data-share-id="${itemId}"]`;
                const element = document.querySelector(selector);
                if (element) {
                    element.remove();
                }
                closeDeleteConfirmModal();
            } else {
                const error = await response.json();
                alert(error.message || `Failed to delete ${itemType}`);
            }
        } catch (error) {
            console.error(`Error deleting ${itemType}:`, error);
            alert(`Failed to delete ${itemType}`);
        }
    }

    // Close modals on outside click
    document.getElementById('editPostModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditPostModal();
    });
    document.getElementById('editShareModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditShareModal();
    });
    document.getElementById('deleteConfirmModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteConfirmModal();
    });

    // Friend Dropdown Toggle
    const friendDropdownBtn = document.getElementById('friendDropdownBtn');
    const friendDropdown = document.getElementById('friendDropdown');
    
    if (friendDropdownBtn && friendDropdown) {
        friendDropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            friendDropdown.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!friendDropdown.contains(e.target) && !friendDropdownBtn.contains(e.target)) {
                friendDropdown.classList.add('hidden');
            }
        });
    }

    // Send Friend Request
    async function sendFriendRequest(userId) {
        try {
            const response = await fetch(`/friends/${userId}/send`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Replace button with "Request Sent"
                const btn = document.getElementById('addFriendBtn');
                if (btn) {
                    btn.outerHTML = `
                        <button id="cancelRequestBtn" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Request Sent
                        </button>
                    `;
                }
            } else {
                alert(data.message || 'Failed to send friend request');
            }
        } catch (error) {
            console.error('Error sending friend request:', error);
            alert('Failed to send friend request');
        }
    }

    // Cancel Friend Request
    async function cancelFriendRequest(friendshipId) {
        try {
            const response = await fetch(`/friends/${friendshipId}/cancel`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to cancel request');
            }
        } catch (error) {
            console.error('Error cancelling request:', error);
            alert('Failed to cancel request');
        }
    }

    // Accept Friend Request
    async function acceptFriendRequest(friendshipId) {
        try {
            const response = await fetch(`/friends/${friendshipId}/accept`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to accept request');
            }
        } catch (error) {
            console.error('Error accepting request:', error);
            alert('Failed to accept request');
        }
    }

    // Reject Friend Request
    async function rejectFriendRequest(friendshipId) {
        try {
            const response = await fetch(`/friends/${friendshipId}/reject`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to reject request');
            }
        } catch (error) {
            console.error('Error rejecting request:', error);
            alert('Failed to reject request');
        }
    }

    // Unfriend
    async function unfriend(userId) {
        if (!confirm('Are you sure you want to unfriend this person?')) return;
        
        try {
            const response = await fetch(`/friends/${userId}/remove`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to unfriend');
            }
        } catch (error) {
            console.error('Error unfriending:', error);
            alert('Failed to unfriend');
        }
    }

    // Block User (placeholder - needs backend implementation)
    async function blockUser(userId) {
        if (!confirm('Are you sure you want to block this person? They won\'t be able to see your profile or contact you.')) return;
        
        try {
            const response = await fetch(`/users/${userId}/block`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();            
            if (data.success) {
                alert('User blocked successfully');
                window.location.href = '/';
            } else {
                alert(data.message || 'Failed to block user');
            }
        } catch (error) {
            console.error('Error blocking user:', error);
            alert('Block feature coming soon');
        }
    }
