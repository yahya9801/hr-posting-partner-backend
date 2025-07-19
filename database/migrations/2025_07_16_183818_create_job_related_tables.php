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
        // Main jobs table
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_title'); // Replaces company_name
            $table->longText('description');
            $table->string('image_path')->nullable(); // Stores image URL or path
            $table->date('posted_at')->default(now());
            $table->timestamps();
        });

        // Locations (Lahore, Karachi, etc.)
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Roles (Frontend Developer, etc.)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Pivot: job_location
        Schema::create('job_location', function (Blueprint $table) {
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->primary(['job_id', 'location_id']);
        });

        // Pivot: job_role
        Schema::create('job_role', function (Blueprint $table) {
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->primary(['job_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_related_tables');
    }
};
