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
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('job_title');
            $table->string('short_description', 300)->nullable()->after('description');
            $table->date('expiry_date')->nullable()->after('posted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['slug', 'short_description', 'expiry_date']);
        });
    }
};
