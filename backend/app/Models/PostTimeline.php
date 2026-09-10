<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTimeline extends Model
{
    protected $fillable = ['profile_id', 'captions', 'comment', 'likes'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}