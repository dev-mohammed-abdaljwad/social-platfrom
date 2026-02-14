<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Post\SharePostRequest;
use App\Http\Requests\Web\Post\StorePostRequest;
use App\Http\Requests\Web\Post\UpdatePostRequest;
use App\Http\Requests\Web\Post\UpdateShareRequest;
use App\Services\Like\LikeService;
use App\Services\Notification\NotificationService;
use App\Services\Post\PostService;
use App\Services\Share\ShareService;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService,
        protected LikeService $likeService,
        protected ShareService $shareService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Store a new post.
     */
    public function store(StorePostRequest $request)
    {
        $this->postService->createWithMedia(
            auth()->user(),
            $request->validated(),
            $request->file('image'),
            $request->file('video')
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Post created!');
    }

    /**
     * Toggle like on a post.
     */
    public function toggleLike(int $postId)
    {
        $post = $this->postService->find($postId);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $result = $this->likeService->togglePostLike(auth()->user(), $postId);

        // Send notification to post owner (if not self and liked)
        if ($result['liked'] && $post->user_id !== auth()->id()) {
            $this->notificationService->postLiked(
                $post->user,
                auth()->user(),
                $post,
                $result['like']
            );
        }

        return response()->json([
            'success' => true,
            'liked' => $result['liked'],
            'likes_count' => $result['likes_count'],
        ]);
    }

    /**
     * Share a post.
     */
    public function share(SharePostRequest $request, int $postId)
    {
        $result = $this->shareService->toggleShare(
            auth()->user(),
            $postId,
            $request->validated()['content'] ?? null
        );

        return response()->json([
            'success' => true,
            'shared' => $result['shared'],
            'shares_count' => $result['shares_count'],
        ]);
    }

    /**
     * Toggle save on a post.
     */
    public function toggleSave(int $postId)
    {
        $post = $this->postService->find($postId);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $result = $this->postService->toggleSave(auth()->user(), $post);

        return response()->json([
            'success' => true,
            'saved' => $result['saved'],
        ]);
    }

    /**
     * Get likes for a post.
     */
    public function getLikes(int $postId)
    {
        $post = $this->postService->find($postId);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $likes = $this->postService->getFormattedLikes($post);

        return response()->json([
            'success' => true,
            'likes' => $likes,
        ]);
    }

    /**
     * Update a post.
     */
    public function update(UpdatePostRequest $request, int $postId)
    {
        $post = $this->postService->find($postId);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        if (!$this->postService->canModify($post->user_id, auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $this->postService->update($post, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'post' => $this->postService->formatPost($post->fresh()),
        ]);
    }

    /**
     * Delete a post.
     */
    public function destroy(int $postId)
    {
        $post = $this->postService->find($postId);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        if (!$this->postService->canModify($post->user_id, auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $this->postService->deleteWithMedia($post);

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Update a share.
     */
    public function updateShare(UpdateShareRequest $request, int $shareId)
    {
        $share = $this->shareService->find($shareId);

        if (!$share) {
            return response()->json(['success' => false, 'message' => 'Share not found'], 404);
        }

        if (!$this->shareService->canModify($share->user_id, auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $this->shareService->update($share, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Share updated successfully',
            'share' => $this->shareService->formatShare($share->fresh()),
        ]);
    }

    /**
     * Delete a share.
     */
    public function destroyShare(int $shareId)
    {
        $share = $this->shareService->find($shareId);

        if (!$share) {
            return response()->json(['success' => false, 'message' => 'Share not found'], 404);
        }

        if (!$this->shareService->canModify($share->user_id, auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $this->shareService->delete($share);

        return response()->json([
            'success' => true,
            'message' => 'Share deleted successfully',
        ]);
    }
}
