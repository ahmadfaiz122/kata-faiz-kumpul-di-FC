<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = ['profile_id', 'name', 'levels', 'category', 'description', 'validated_upload', 'certificate_path', 'tanggal_terbit', 'kadaluwarsa'];

    protected function casts(): array
    {
        return [
            'validated_upload' => 'boolean',
            'tanggal_terbit' => 'date:Y-m-d',
            'kadaluwarsa' => 'date:Y-m-d',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}