<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get all jobs associated with this experience level.
     */
    public function jobs()
    {
        return $this->belongsToMany(Job::class);
    }
}
