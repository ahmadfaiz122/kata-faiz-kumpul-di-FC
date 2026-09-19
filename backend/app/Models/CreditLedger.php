<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditLedger extends Model
{
    protected $table = 'credit_ledger';

    protected $fillable = ['user_id', 'transaction_id', 'amount', 'balance_after', 'type', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
