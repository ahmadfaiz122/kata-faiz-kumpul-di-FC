<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'barter_request', 'provider', 'requester', 'requester_skill_id', 'mode',
        'hour', 'credits', 'status', 'approved_at', 'starts_at', 'ends_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function barterRequest()
    {
        return $this->belongsTo(SkillRequest::class, 'barter_request');
    }

    public function providerUser()
    {
        return $this->belongsTo(User::class, 'provider');
    }

    public function requesterUser()
    {
        return $this->belongsTo(User::class, 'requester');
    }

    public function requesterSkill()
    {
        return $this->belongsTo(Skill::class, 'requester_skill_id');
    }

    public function review()
    {
        return $this->hasOne(TransactionReview::class);
    }
}
