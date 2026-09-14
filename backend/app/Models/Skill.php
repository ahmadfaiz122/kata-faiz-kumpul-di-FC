<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['profile_id', 'name', 'category_skills', 'description', 'material_path'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}