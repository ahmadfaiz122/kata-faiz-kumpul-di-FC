<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['transaction_id'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
