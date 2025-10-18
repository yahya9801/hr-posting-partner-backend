<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPostApiController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $searchTerm = trim((string) $request->input('q', ''));

        if ($searchTerm === '') {
            return response()->json([
                'data' => [],
            ]);
        }

        $limit = (int) $request->input('limit', 10);
        $limit = $limit > 0 ? min($limit, 50) : 10;
        $likeTerm = '%' . $searchTerm . '%';

        $posts = BlogPost::query()
            ->with(['category:id,name,slug'])
            ->where('status', BlogPost::STATUS_PUBLISHED)
            ->where(function ($query) use ($likeTerm) {
                $query->where('title', 'like', $likeTerm)
                    ->orWhere('content', 'like', $likeTerm)
                    ->orWhereHas('category', function ($categoryQuery) use ($likeTerm) {
                        $categoryQuery->where('name', 'like', $likeTerm);
                    });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();

        $data = $posts->map(function (BlogPost $post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => Str::limit(strip_tags((string) $post->content), 160),
                'image_url' => $this->resolveImageUrl($post->featured_image_path),
                'published_at' => $this->formatIsoDate($post->published_at ?? $post->created_at),
                'published_at_readable' => $this->formatDisplayDate($post),
                'category' => $post->category ? [
                    'id' => $post->category->id,
                    'name' => $post->category->name,
                    'slug' => $post->category->slug,
                ] : null,
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

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

        $isoPublishedAt = $this->formatIsoDate($post->published_at);
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

    private function formatIsoDate(?Carbon $timestamp): ?string
    {
        if (!$timestamp) {
            return null;
        }

        return $timestamp
            ->timezone(config('app.timezone'))
            ->toDateString();
    }

    private function formatDisplayDate(BlogPost $post): ?string
    {
        $timestamp = $post->published_at ?? $post->created_at;

        if (!$timestamp) {
            return null;
        }

        return $timestamp->timezone(config('app.timezone'))
            ->format('M d, Y');
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
