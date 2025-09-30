<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title', 100);
            $table->string('slug', 150)->unique();
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt', 120)->nullable();
            $table->json('tags')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title', 120)->nullable();
            $table->string('og_description', 200)->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('noindex')->default(false);
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedSmallInteger('reading_time_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
