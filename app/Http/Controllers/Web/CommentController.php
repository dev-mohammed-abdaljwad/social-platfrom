<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Store a new comment.
     */
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $post->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Send notification to post owner (if not self)
        if ($post->user_id !== auth()->id()) {
            $this->notificationService->postCommented(
                $post->user,
                auth()->user(),
                $post,
                $comment
            );
        }

        if ($request->expectsJson()) {
            $comment->load('user');
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'user' => [
                        'name' => $comment->user->name,
                        'avatar_url' => $comment->user->avatar_url,
                    ],
                    'likes_count' => 0,
                    'is_liked' => false,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Comment added!');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment)
    {
        // Only comment owner can delete
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Comment deleted!');
    }

    /**
     * Get comments for a post (AJAX).
     */
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->with('user')
            ->withCount('likes')
            ->whereNull('parent_id')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar_url' => $comment->user->avatar_url,
                    ],
                    'is_owner' => auth()->check() && auth()->id() === $comment->user_id,
                    'likes_count' => $comment->likes_count,
                    'is_liked' => auth()->check() && $comment->likes()->where('user_id', auth()->id())->exists(),
                ];
            });

        return response()->json(['success' => true, 'comments' => $comments]);
    }

    /**
     * Toggle like on a comment.
     */
    public function toggleLike(Comment $comment)
    {
        $user = auth()->user();
        $existingLike = $comment->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            $comment->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $comment->likes()->count(),
        ]);
    }
}
