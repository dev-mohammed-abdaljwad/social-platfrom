<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Store a new post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'video' => 'nullable|mimetypes:video/mp4,video/mpeg,video/quicktime,video/webm|max:102400', // 100MB max
            'location' => 'nullable|string|max:255',
            'privacy' => 'nullable|in:public,friends,private',
        ]);

        // At least one of content, image, or video is required
        if (empty($validated['content']) && !$request->hasFile('image') && !$request->hasFile('video')) {
            return back()->withErrors(['content' => 'Please provide text, image, or video for your post.']);
        }

        // Only allow one media type per post (video takes priority)
        $type = 'text';
        $imagePath = null;
        $videoPath = null;

        // Handle video upload (priority over image)
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('posts/videos', 'public');
            $type = 'video';
        }
        // Handle image upload (only if no video)
        elseif ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts/images', 'public');
            $type = 'image';
        }

        Post::create([
            'user_id' => auth()->id(),
            'content' => $validated['content'] ?? null,
            'image' => $imagePath,
            'video' => $videoPath,
            'location' => $validated['location'] ?? null,
            'privacy' => $validated['privacy'] ?? 'public',
            'type' => $type,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Post created!');
    }

    /**
     * Toggle like on a post.
     */
    public function toggleLike(Post $post)
    {
        $like = $post->likes()->where('user_id', auth()->id())->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            $post->likes()->create(['user_id' => auth()->id()]);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ]);
    }

    /**
     * Share a post.
     */
    public function share(Request $request, Post $post)
    {
        $user = auth()->user();

        // Check if user already shared this post
        $existingShare = $post->shares()->where('user_id', $user->id)->first();

        if ($existingShare) {
            // Toggle: unshare
            $existingShare->delete();
            return response()->json([
                'success' => true,
                'shared' => false,
                'shares_count' => $post->shares()->count(),
            ]);
        }

        // Create share
        $validated = $request->validate([
            'content' => 'nullable|string|max:1000',
        ]);

        $post->shares()->create([
            'user_id' => $user->id,
            'content' => $validated['content'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'shared' => true,
            'shares_count' => $post->shares()->count(),
        ]);
    }

    /**
     * Toggle save on a post.
     */
    public function toggleSave(Post $post)
    {
        $user = auth()->user();
        
        if ($post->isSavedBy($user)) {
            $user->savedPosts()->detach($post->id);
            $saved = false;
        } else {
            $user->savedPosts()->attach($post->id);
            $saved = true;
        }

        return response()->json([
            'success' => true,
            'saved' => $saved,
        ]);
    }
}
