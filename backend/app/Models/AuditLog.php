<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_id',
        'transaction_id',
        'action',
        'from_status',
        'to_status',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
