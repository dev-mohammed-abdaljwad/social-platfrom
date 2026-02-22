<?php

namespace App\Models;

use App\Enums\ReactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reactable_id',
        'reactable_type',
        'type', // e.g., like, love, haha, etc.
    ];

    protected function casts(): array
    {
        return [
            // 'type' => ReactionTypeEnum::class,
        ];
    }
    public function reactable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function ($reaction) {
            if ($reaction->reactable_type === Post::class) {
                // Using morph relation to touch the post's counter
                Post::where('id', $reaction->reactable_id)->increment('likes_count');
            }
        });

        static::deleted(function ($reaction) {
            if ($reaction->reactable_type === Post::class) {
                Post::where('id', $reaction->reactable_id)->decrement('likes_count');
            }
        });
    }
}
