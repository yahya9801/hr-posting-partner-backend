<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title',
                'meta_description',
                'featured_image',
                'featured_image_alt',
                'tags',
                'canonical_url',
                'og_title',
                'og_description',
                'og_image',
                'noindex',
                'word_count',
                'reading_time_minutes',
            ]);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('author_id')->constrained('blog_categories')->nullOnDelete();
            $table->string('featured_image_path')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'featured_image_path']);

            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt', 120)->nullable();
            $table->json('tags')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title', 120)->nullable();
            $table->string('og_description', 200)->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('noindex')->default(false);
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedSmallInteger('reading_time_minutes')->default(0);
        });
    }
};
