<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionReview extends Model
{
    protected $fillable = ['transaction_id', 'reviewer', 'reviewed', 'rating', 'reputation', 'comment'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
