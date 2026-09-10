<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['barter_request', 'provider', 'requester', 'hour', 'credits', 'status'];

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
}