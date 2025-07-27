<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Job extends Model
{
    protected $fillable = [
        'job_title',
        'description',
        'image_path',
        'posted_at',
        'expiry_date',
        'short_description',
        'experience'
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'job_location');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'job_role');
    }

    public function experiences()
    {
        return $this->belongsToMany(Experience::class);
    }

    public function images()
    {
        return $this->hasMany(JobImages::class);
    }


    protected static function booted()
    {
        static::creating(function ($job) {
            $job->slug = static::generateUniqueSlug($job->job_title);
        });

        static::updating(function ($job) {
            if ($job->isDirty('job_title')) {
                $job->slug = static::generateUniqueSlug($job->job_title);
            }
        });
    }

    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}

