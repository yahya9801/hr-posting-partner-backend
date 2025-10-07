<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPostApiController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::query()
            ->with([
                'category:id,name,slug',
                'author:id,name',
            ])
            ->where('slug', $slug)
            ->where('status', BlogPost::STATUS_PUBLISHED)
            ->first();

        if (!$post) {
            return response()->json(['message' => 'Blog post not found'], 404);
        }

        $contentHtml = clean($post->content);
        $plainExcerpt = Str::limit(strip_tags($contentHtml), 160);

        $isoPublishedAt = optional($post->published_at)->toIso8601String();
        $displayPublishedAt = $this->formatDisplayDate($post);

        $baseUrl = config('app.url');
        $baseUrl = $baseUrl ? rtrim($baseUrl, '/') : null;
        if ($baseUrl === null || str_contains($baseUrl, 'localhost')) {
            $baseUrl = 'https://admin.hrpostingpartner.com';
        }

        $canonicalUrl = sprintf('%s/blog/%s', $baseUrl, $post->slug);
        $imageUrl = $this->resolveImageUrl($post->featured_image_path);

        return response()->json([
            'data' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content_html' => $contentHtml,
                'excerpt' => $plainExcerpt,
                'image_url' => $imageUrl,
                'published_at' => $isoPublishedAt,
                'published_at_readable' => $displayPublishedAt,
                'category' => $post->category ? [
                    'id' => $post->category->id,
                    'name' => $post->category->name,
                    'slug' => $post->category->slug,
                ] : null,
                'author' => $post->author ? [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                ] : null,
                'seo' => [
                    'title' => $post->title,
                    'description' => Str::limit(strip_tags($contentHtml), 155),
                    'canonical_url' => $canonicalUrl,
                    'image' => $imageUrl,
                    'published_at' => $isoPublishedAt,
                ],
            ],
        ]);
    }

    private function formatDisplayDate(BlogPost $post): ?string
    {
        $timestamp = $post->published_at ?? $post->created_at;

        if (!$timestamp) {
            return null;
        }

        return $timestamp->timezone(config('app.timezone'))
            ->format('M d, Y H:i');
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $baseUrl = config('app.asset_url') ?: config('app.url');
        $baseUrl = $baseUrl ? rtrim($baseUrl, '/') : null;

        if ($baseUrl === null || str_contains($baseUrl, 'localhost')) {
            $baseUrl = 'https://admin.hrpostingpartner.com';
        }

        if (!$baseUrl) {
            $storageUrl = Storage::disk('public')->url($path);
            if (preg_match('#^https?://#i', $storageUrl)) {
                return $storageUrl;
            }

            return url($storageUrl);
        }

        return sprintf('%s/storage/%s', $baseUrl, ltrim($path, '/'));
    }
}
