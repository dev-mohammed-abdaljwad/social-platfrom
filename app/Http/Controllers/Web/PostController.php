<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Post\SharePostRequest;
use App\Http\Requests\Web\Post\StorePostRequest;
use App\Http\Requests\Web\Post\UpdatePostRequest;
use App\Http\Requests\Web\Post\UpdateShareRequest;
use App\Services\Notification\NotificationService;
use App\Services\Post\PostService;
use App\Services\Share\ShareService;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService,
    
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'shared' => $result['shared'],
                'shares_count' => $result['shares_count'],
            ]);
        }

        return redirect()->back()->with('success', $result['shared'] ? 'Post shared!' : 'Share removed!');
    }

    /**
     * Toggle save on a post.
     */
    public function toggleSave(int $postId)
    {
        $post = $this->postService->find($postId);

        if (!$post) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }
            return redirect()->back()->with('error', 'Post not found');
        }

        $result = $this->postService->toggleSave(auth()->user(), $post);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'saved' => $result['saved'],
            ]);
        }

        return redirect()->back()->with('success', $result['saved'] ? 'Post saved!' : 'Post unsaved!');
    }

    /**
     * Get likes for a post.
     */
    

    /**
     * Update a post.
     */
    public function update(UpdatePostRequest $request, int $postId)
    {
        $post = $this->postService->find($postId);

        if (!$post) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }
            return redirect()->back()->with('error', 'Post not found');
        }

        if (!$this->postService->canModify($post->user_id, auth()->id())) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $this->postService->update($post, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'post' => $this->postService->formatPost($post->fresh()),
            ]);
        }

        return redirect()->back()->with('success', 'Post updated successfully!');
    }

    /**
     * Delete a post.
     */
    public function destroy(int $postId)
    {
        $post = $this->postService->find($postId);

        if (!$post) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }
            return redirect()->back()->with('error', 'Post not found');
        }

        if (!$this->postService->canModify($post->user_id, auth()->id())) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $this->postService->deleteWithMedia($post);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
            ]);
        }

        return redirect()->back()->with('success', 'Post deleted successfully!');
    }

    /**
     * Update a share.
     */
    public function updateShare(UpdateShareRequest $request, int $shareId)
    {
        $share = $this->shareService->find($shareId);

        if (!$share) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Share not found'], 404);
            }
            return redirect()->back()->with('error', 'Share not found');
        }

        if (!$this->shareService->canModify($share->user_id, auth()->id())) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $this->shareService->update($share, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Share updated successfully',
                'share' => $this->shareService->formatShare($share->fresh()),
            ]);
        }

        return redirect()->back()->with('success', 'Share updated successfully!');
    }

    /**
     * Delete a share.
     */
    public function destroyShare(int $shareId)
    {
        $share = $this->shareService->find($shareId);

        if (!$share) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Share not found'], 404);
            }
            return redirect()->back()->with('error', 'Share not found');
        }

        if (!$this->shareService->canModify($share->user_id, auth()->id())) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $this->shareService->delete($share);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Share deleted successfully',
            ]);
        }

        return redirect()->back()->with('success', 'Share deleted successfully!');
    }
}
