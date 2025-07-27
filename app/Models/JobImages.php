<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobImages extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'image_path',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
