<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'alias',
        'bio',
        'nim',
        'linkedin',
        'github',
        'instagram',
        'rating',
        'reputation',
        'leaderboard',
        'credits',
        'social_media',
        'photo_profile',
        'skills',
        'achievements',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'achievements' => 'array',
            'social_media' => 'array',
            'rating' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skillRecords()
    {
        return $this->hasMany(Skill::class);
    }

    public function achievementRecords()
    {
        return $this->hasMany(Achievement::class);
    }

    public function posts()
    {
        return $this->hasMany(PostTimeline::class);
    }
}
