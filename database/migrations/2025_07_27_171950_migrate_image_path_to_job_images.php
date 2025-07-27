<?php

use App\Models\Job;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $jobs = Job::whereNotNull('image_path')->get();

        foreach ($jobs as $job) {
            DB::table('job_images')->insert([
                'job_id' => $job->id,
                'image_path' => $job->image_path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_images', function (Blueprint $table) {
            //
        });
    }
};
