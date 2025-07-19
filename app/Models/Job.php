<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = [
        'job_title',
        'description',
        'image_path',
        'posted_at',
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'job_location');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'job_role');
    }
}

