<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillRequest extends Model
{
    protected $table = 'requests';
    protected $fillable = ['skill_id', 'requester', 'new_column', 'status', 'hour'];

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function requesterUser()
    {
        return $this->belongsTo(User::class, 'requester');
    }
}