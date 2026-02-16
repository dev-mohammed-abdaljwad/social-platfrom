// public/js/modules/feed.js

// ==========================================
// INFINITE SCROLL
// ==========================================
let lastPostId = window.INITIAL_LAST_POST_ID || null;
let isLoading = false;
let hasMore = window.INITIAL_HAS_MORE || false;

const postsFeed = document.getElementById('postsFeed');
const loadingIndicator = document.getElementById('loadingIndicator');
const loadingSpinner = document.getElementById('loadingSpinner');
const endOfFeed = document.getElementById('endOfFeed');

const observerCallback = async (entries) => {
    const entry = entries[0];
    if (entry.isIntersecting && !isLoading && hasMore) {
        await loadMorePosts();
    }
};

const observer = new IntersectionObserver(observerCallback, {
    root: null,
    rootMargin: '200px',
    threshold: 0
});

if (loadingIndicator && hasMore) {
    observer.observe(loadingIndicator);
}

async function loadMorePosts() {
    if (isLoading || !hasMore || !lastPostId) return;
    
    isLoading = true;
    loadingSpinner.classList.remove('hidden');
    loadingSpinner.classList.add('flex');
    
    try {
        const response = await fetch(`/posts/feed?last_id=${lastPostId}&limit=10`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.html && data.html.trim()) {
                loadingIndicator.insertAdjacentHTML('beforebegin', data.html);
                lastPostId = data.last_id;
                hasMore = data.has_more;
            } else {
                hasMore = false;
            }
            
            if (!hasMore) {
                loadingIndicator.classList.add('hidden');
                endOfFeed?.classList.remove('hidden');
                observer.disconnect();
            }
        }
    } catch (error) {
        console.error('Error loading posts:', error);
    } finally {
        isLoading = false;
        loadingSpinner.classList.add('hidden');
        loadingSpinner.classList.remove('flex');
    }
}

// ==========================================
// SCROLL TO POST FROM NOTIFICATION
// ==========================================
window.addEventListener('load', async function() {
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('post');
    
    if (postId) {
        const attemptScroll = async () => {
            const postElement = document.getElementById(`post-${postId}`);
            if (postElement) {
                postElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                postElement.classList.add('ring-2', 'ring-blue-500', 'ring-opacity-50');
                setTimeout(() => {
                    postElement.classList.remove('ring-2', 'ring-blue-500', 'ring-opacity-50');
                }, 3000);
                return true;
            }
            return false;
        };
        
        setTimeout(async () => {
            const found = await attemptScroll();
            if (!found) setTimeout(attemptScroll, 1500);
        }, 500);
    }
});