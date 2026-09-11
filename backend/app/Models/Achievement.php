<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = ['profile_id', 'name', 'levels', 'category', 'validated_upload', 'tanggal_terbit', 'kadaluwarsa'];

    protected function casts(): array
    {
        return ['validated_upload' => 'boolean'];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}