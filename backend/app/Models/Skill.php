<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['profile_id', 'category_id', 'name', 'category_skills', 'description', 'material_path', 'status'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}