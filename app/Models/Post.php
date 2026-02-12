<?php

namespace App\Models;

use App\Enums\ContentTypeEnum;
use App\Enums\PrivacyTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    protected function casts(): array
    {
        return [
            'privacy' => PrivacyTypeEnum::class,
            'type' => ContentTypeEnum::class,
        ];
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
