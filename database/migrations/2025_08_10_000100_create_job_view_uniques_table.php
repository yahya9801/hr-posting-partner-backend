<?php

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
        if (!Schema::hasTable('job_view_uniques')) {
            Schema::create('job_view_uniques', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
                $table->string('fingerprint', 64);
                $table->date('day');
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['job_id', 'fingerprint', 'day'], 'job_fp_day_unique');
                $table->index(['job_id', 'day']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_view_uniques');
    }
};
