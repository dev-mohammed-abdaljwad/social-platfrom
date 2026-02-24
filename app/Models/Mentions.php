<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mentions extends Model
{
    use HasFactory;
    protected $table = 'mentions';

    protected $fillable = [
        'mentioned_user_id',
        'mentioner_id',
        'mentionable_type',
        'mentionable_id',
    ];

    protected function casts(): array
    {
        return [
            'mentioned_user_id' => 'integer',
            'mentioner_id' => 'integer',
        ];
    }

    public function mentionedUser()
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }

    public function mentioner()
    {
        return $this->belongsTo(User::class, 'mentioner_id');
    }

    public function mentionable()
    {
        return $this->morphTo();
    }
}
