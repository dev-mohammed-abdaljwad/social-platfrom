<?php

namespace App\Models;

use App\Enums\ContentTypeEnum;
use App\Enums\PrivacyTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'image',
        'video',
        'location',
        'privacy',
        'type',
    ];

    protected $appends = ['image_url', 'video_url'];

    protected function casts(): array
    {
        return [
            'privacy' => PrivacyTypeEnum::class,
            'type' => ContentTypeEnum::class,
        ];
    }

    /**
     * Get the full URL for the post image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // If already a full URL, return as is
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Get the full URL for the post video.
     */
    public function getVideoUrlAttribute(): ?string
    {
        if (!$this->video) {
            return null;
        }

        // If already a full URL, return as is
        if (filter_var($this->video, FILTER_VALIDATE_URL)) {
            return $this->video;
        }

        return asset('storage/' . $this->video);
    }

    /**
     * Get the user who created the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get root level comments (not replies).
     */
    public function rootComments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    /**
     * Get all likes for the post.
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Get all shares for the post.
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Get the number of likes for this post.
     */
    public function getLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }

    /**
     * Get the number of comments for this post.
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }

    /**
     * Get the number of shares for this post.
     */
    public function getSharesCountAttribute(): int
    {
        return $this->shares()->count();
    }

    /**
     * Check if a user has shared this post.
     */
    public function isSharedBy(User $user): bool
    {
        return $this->shares()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user has liked this post.
     */
    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if the post is public.
     */
    public function isPublic(): bool
    {
        return $this->privacy === PrivacyTypeEnum::Public;
    }

    /**
     * Check if the post is private.
     */
    public function isPrivate(): bool
    {
        return $this->privacy === PrivacyTypeEnum::Privet;
    }

    /**
     * Check if the post is visible to friends only.
     */
    public function isFriendsOnly(): bool
    {
        return $this->privacy === PrivacyTypeEnum::Friends;
    }

    /**
     * Scope for public posts.
     */
    public function scopePublic($query)
    {
        return $query->where('privacy', PrivacyTypeEnum::Public);
    }

    /**
     * Scope for posts visible to a user (own posts, public, or friends).
     */
    public function scopeVisibleTo($query, User $user)
    {
        $friendIds = $user->friends()->pluck('id')->toArray();

        return $query->where(function ($q) use ($user, $friendIds) {
            $q->where('user_id', $user->id)
                ->orWhere('privacy', PrivacyTypeEnum::Public)
                ->orWhere(function ($q) use ($friendIds) {
                    $q->where('privacy', PrivacyTypeEnum::Friends)
                        ->whereIn('user_id', $friendIds);
                });
        });
    }
}
