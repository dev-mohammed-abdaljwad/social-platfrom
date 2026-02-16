// public/js/modules/posts.js

// ==========================================
// SHARE FUNCTIONALITY
// ==========================================
let currentSharePostId = null;
let currentShareBtn = null;

document.addEventListener('click', function(e) {
    const shareBtn = e.target.closest('.share-btn');
    if (shareBtn) {
        const postId = shareBtn.dataset.postId;
        currentSharePostId = postId;
        currentShareBtn = shareBtn;
        
        if (shareBtn.classList.contains('text-green-500')) {
            unsharePost(postId, shareBtn);
            return;
        }
        
        const postCard = document.querySelector(`[data-post-id="${postId}"]`);
        const userName = postCard.querySelector('a.font-semibold')?.textContent || 'User';
        const postContent = postCard.querySelector('.post-content')?.textContent || '';
        const postImage = postCard.querySelector('img[alt="Post image"]')?.src || '';
        
        let previewHTML = `<div class="flex items-center gap-2 mb-2"><span class="font-semibold text-sm text-gray-800">${userName}</span></div>`;
        if (postContent) previewHTML += `<p class="text-gray-700 text-sm line-clamp-3">${postContent}</p>`;
        if (postImage) previewHTML += `<img src="${postImage}" class="mt-2 rounded max-h-32 object-cover" alt="Preview">`;
        
        document.getElementById('sharePostPreview').innerHTML = previewHTML;
        document.getElementById('shareContent').value = '';
        document.getElementById('shareModal').classList.remove('hidden');
    }
});

function closeShareModal() {
    document.getElementById('shareModal').classList.add('hidden');
    currentSharePostId = null;
    currentShareBtn = null;
}

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

// ==========================================
// SAVE FUNCTIONALITY
// ==========================================
document.addEventListener('click', async function(e) {
    const saveBtn = e.target.closest('.save-btn');
    if (saveBtn) {
        const postId = saveBtn.dataset.postId;
        try {
            const response = await fetch(`/posts/${postId}/save`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                const data = await response.json();
                const svg = saveBtn.querySelector('svg');
                
                if (data.saved) {
                    saveBtn.classList.add('text-yellow-500');
                    svg.setAttribute('fill', 'currentColor');
                } else {
                    saveBtn.classList.remove('text-yellow-500');
                    svg.setAttribute('fill', 'none');
                }
            }
        } catch (error) {
            console.error('Error saving post:', error);
        }
    }
});

// ==========================================
// POST MENU & EDIT/DELETE
// ==========================================
document.addEventListener('click', function(e) {
    const menuBtn = e.target.closest('.post-menu-btn');
    if (menuBtn) {
        const postId = menuBtn.dataset.postId;
        const menu = document.querySelector(`.post-menu[data-post-id="${postId}"]`);
        
        document.querySelectorAll('.post-menu').forEach(m => {
            if (m !== menu) m.classList.add('hidden');
        });
        
        menu.classList.toggle('hidden');
        return;
    }
    
    if (!e.target.closest('.post-menu')) {
        document.querySelectorAll('.post-menu').forEach(m => m.classList.add('hidden'));
    }
});

document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.edit-post-btn');
    if (editBtn) {
        const postId = editBtn.dataset.postId;
        const content = editBtn.dataset.content || '';
        const location = editBtn.dataset.location || '';
        const privacy = editBtn.dataset.privacy || 'public';
        
        openEditPostModal(postId, content, location, privacy);
        document.querySelectorAll('.post-menu').forEach(m => m.classList.add('hidden'));
    }
});

document.addEventListener('click', function(e) {
    const deleteBtn = e.target.closest('.delete-post-btn');
    if (deleteBtn) {
        const postId = deleteBtn.dataset.postId;
        openDeleteModal(postId);
        document.querySelectorAll('.post-menu').forEach(m => m.classList.add('hidden'));
    }
});

function openEditPostModal(postId, content, location, privacy) {
    document.getElementById('editPostId').value = postId;
    document.getElementById('editPostContent').value = content;
    document.getElementById('editPostLocation').value = location;
    document.getElementById('editPostPrivacy').value = privacy;
    document.getElementById('editPostModal').classList.remove('hidden');
}

function closeEditPostModal() {
    document.getElementById('editPostModal').classList.add('hidden');
}

async function saveEditPost() {
    const postId = document.getElementById('editPostId').value;
    const content = document.getElementById('editPostContent').value;
    const location = document.getElementById('editPostLocation').value;
    const privacy = document.getElementById('editPostPrivacy').value;
    
    try {
        const response = await fetch(`/posts/${postId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ content, location, privacy })
        });
        
        if (response.ok) {
            const postCard = document.querySelector(`[data-post-id="${postId}"]`);
            const postContent = postCard.querySelector('.post-content');
            if (postContent) postContent.textContent = content;
            
            const editBtn = postCard.querySelector('.edit-post-btn');
            if (editBtn) {
                editBtn.dataset.content = content;
                editBtn.dataset.location = location;
                editBtn.dataset.privacy = privacy;
            }
            closeEditPostModal();
        } else {
            const error = await response.json();
            alert(error.message || 'Failed to update post');
        }
    } catch (error) {
        console.error('Error updating post:', error);
        alert('Error updating post');
    }
}

function openDeleteModal(postId) {
    document.getElementById('deletePostId').value = postId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

async function confirmDelete() {
    const postId = document.getElementById('deletePostId').value;
    
    try {
        const response = await fetch(`/posts/${postId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (response.ok) {
            const postCard = document.querySelector(`[data-post-id="${postId}"]`);
            if (postCard) postCard.remove();
            closeDeleteModal();
        } else {
            const error = await response.json();
            alert(error.message || 'Failed to delete post');
        }
    } catch (error) {
        console.error('Error deleting post:', error);
        alert('Error deleting post');
    }
}

document.getElementById('editPostModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditPostModal();
});
document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
document.getElementById('shareModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeShareModal();
});