<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'message',
        'data',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Notification types.
     */
    public const TYPE_FRIEND_REQUEST = 'friend_request';
    public const TYPE_FRIEND_ACCEPTED = 'friend_accepted';

    public const TYPE_COMMENT = 'comment';
    public const TYPE_REPLY = 'reply';
    public const TYPE_REACTION = 'reaction';
    public const TYPE_MESSAGE = 'message';
    public const TYPE_MENTION_IN_POST = 'mention_in_post';
    public const TYPE_MENTION_IN_COMMENT = 'mention_in_comment';

    /**
     * Get the recipient user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sender user.
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the notifiable entity (post, comment, friendship).
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if notification is read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Get the URL for this notification.
     */
    public function getUrl(): string
    {
        $data = $this->data ?? [];

        return match ($this->type) {
            self::TYPE_COMMENT, self::TYPE_REPLY => isset($data['post_id'])
                ? '/?post=' . $data['post_id'] . (isset($data['comment_id']) ? '#comment-' . $data['comment_id'] : '')
                : '/',
            self::TYPE_MENTION_IN_POST => isset($data['post_id']) ? '/?post=' . $data['post_id'] : '/',
            self::TYPE_MENTION_IN_COMMENT => isset($data['post_id'])
                ? '/?post=' . $data['post_id'] . (isset($data['comment_id']) ? '#comment-' . $data['comment_id'] : '')
                : '/',
            self::TYPE_FRIEND_REQUEST, self::TYPE_FRIEND_ACCEPTED => '/profile/' . $this->from_user_id,
            default => '/',
        };
    }
}
