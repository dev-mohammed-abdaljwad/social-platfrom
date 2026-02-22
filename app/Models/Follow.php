<?php

namespace App\Models;

use App\Enums\FollowStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'followee_id',
        'status',
    ];

    protected $casts = [
        'status' => FollowStatusEnum::class,
    ];

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }
    public function followee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followee_id');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', FollowStatusEnum::Accepted);
    }
    public function scopeRejected($query)
    {
        return $query->where('status', FollowStatusEnum::Rejected);
    }
    public function scopePending($query)
    {
        return $query->where('status', FollowStatusEnum::Pending);
    }
    public function acceptedFollowing()
    {
        return $this->hasMany(Follow::class, 'follower_id')->where('status', FollowStatusEnum::Accepted);
    }
    public function acceptedFollowers()
    {
        return $this->hasMany(Follow::class, 'followee_id')->where('status', FollowStatusEnum::Accepted);
    }
}
