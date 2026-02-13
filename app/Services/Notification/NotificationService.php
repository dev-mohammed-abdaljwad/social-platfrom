<?php

namespace App\Services\Notification;

use App\Events\NewNotification;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Create a notification and broadcast it.
     */
    public function create(
        int $userId,
        int $fromUserId,
        string $type,
        Model $notifiable,
        string $message,
        array $data = []
    ): Notification {
        // Don't notify yourself
        if ($userId === $fromUserId) {
            return new Notification();
        }

        $notification = Notification::create([
            'user_id' => $userId,
            'from_user_id' => $fromUserId,
            'type' => $type,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'message' => $message,
            'data' => $data,
        ]);

        // Broadcast the notification in real-time
        broadcast(new NewNotification($notification))->toOthers();

        return $notification;
    }

    /**
     * Create a friend request notification.
     */
    public function friendRequest(User $recipient, User $sender, Model $friendship): Notification
    {
        return $this->create(
            $recipient->id,
            $sender->id,
            Notification::TYPE_FRIEND_REQUEST,
            $friendship,
            "{$sender->name} sent you a friend request",
            ['friendship_id' => $friendship->id]
        );
    }

    /**
     * Create a friend accepted notification.
     */
    public function friendAccepted(User $recipient, User $accepter, Model $friendship): Notification
    {
        return $this->create(
            $recipient->id,
            $accepter->id,
            Notification::TYPE_FRIEND_ACCEPTED,
            $friendship,
            "{$accepter->name} accepted your friend request",
            ['friendship_id' => $friendship->id]
        );
    }

    /**
     * Create a like notification.
     */
    public function postLiked(User $postOwner, User $liker, Model $post, Model $like): Notification
    {
        return $this->create(
            $postOwner->id,
            $liker->id,
            Notification::TYPE_LIKE,
            $post,
            "{$liker->name} liked your post",
            ['post_id' => $post->id, 'like_id' => $like->id]
        );
    }

    /**
     * Create a comment notification.
     */
    public function postCommented(User $postOwner, User $commenter, Model $post, Model $comment): Notification
    {
        return $this->create(
            $postOwner->id,
            $commenter->id,
            Notification::TYPE_COMMENT,
            $post,
            "{$commenter->name} commented on your post",
            ['post_id' => $post->id, 'comment_id' => $comment->id, 'comment_preview' => substr($comment->content, 0, 100)]
        );
    }

    /**
     * Get user notifications.
     */
    public function getUserNotifications(int $userId, int $limit = 20)
    {
        return Notification::where('user_id', $userId)
            ->with('fromUser')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for user.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->count();
    }

    /**
     * Mark all notifications as read for user.
     */
    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Delete old notifications.
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->whereNotNull('read_at')
            ->delete();
    }
}
