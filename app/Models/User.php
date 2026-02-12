<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\FriendshipStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'cover_photo',
        'bio',
        'phone',
        'is_active',
        'username',
    ];

    protected $appends = ['avatar_url', 'cover_url'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the full URL for the user's avatar.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_picture) {
            // If already a full URL, return as is
            if (filter_var($this->profile_picture, FILTER_VALIDATE_URL)) {
                return $this->profile_picture;
            }
            return asset('storage/' . $this->profile_picture);
        }

        // Default avatar using UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=3b82f6&color=fff';
    }

    /**
     * Get the full URL for the user's cover photo.
     */
    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_photo) {
            if (filter_var($this->cover_photo, FILTER_VALIDATE_URL)) {
                return $this->cover_photo;
            }
            return asset('storage/' . $this->cover_photo);
        }

        return null;
    }

    /**
     * Get all posts by the user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get all comments by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get all likes by the user.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get all shares by the user.
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Get friend requests sent by the user.
     */
    public function sentFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'sender_id');
    }

    /**
     * Get friend requests received by the user.
     */
    public function receivedFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'receiver_id');
    }

    /**
     * Get pending friend requests received by the user.
     */
    public function pendingFriendRequests(): HasMany
    {
        return $this->receivedFriendRequests()->where('status', FriendshipStatusEnum::Pending);
    }

    /**
     * Get all friends of the user.
     */
    public function friends()
    {
        $sentAccepted = $this->sentFriendRequests()
            ->where('status', FriendshipStatusEnum::Accepted)
            ->pluck('receiver_id');

        $receivedAccepted = $this->receivedFriendRequests()
            ->where('status', FriendshipStatusEnum::Accepted)
            ->pluck('sender_id');

        return User::whereIn('id', $sentAccepted->merge($receivedAccepted));
    }

    /**
     * Check if user is friends with another user.
     */
    public function isFriendWith(User $user): bool
    {
        return Friendship::where(function ($query) use ($user) {
            $query->where('sender_id', $this->id)
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $this->id);
        })->where('status', FriendshipStatusEnum::Accepted)->exists();
    }

    /**
     * Check if there's a pending friend request between users.
     */
    public function hasPendingFriendRequestFrom(User $user): bool
    {
        return Friendship::where('sender_id', $user->id)
            ->where('receiver_id', $this->id)
            ->where('status', FriendshipStatusEnum::Pending)
            ->exists();
    }

    /**
     * Check if user has sent a friend request to another user.
     */
    public function hasSentFriendRequestTo(User $user): bool
    {
        return Friendship::where('sender_id', $this->id)
            ->where('receiver_id', $user->id)
            ->where('status', FriendshipStatusEnum::Pending)
            ->exists();
    }
}
