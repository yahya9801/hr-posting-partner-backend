<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogCategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = BlogCategory::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ]);

        $data = $categories->map(function (BlogCategory $category) {
            $posts = BlogPost::query()
                ->select([
                    'id',
                    'category_id',
                    'title',
                    'slug',
                    'featured_image_path',
                    'published_at',
                    'created_at',
                ])
                ->where('status', BlogPost::STATUS_PUBLISHED)
                ->where('category_id', $category->id)
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->take(3)
                ->get();

            if ($posts->isEmpty()) {
                return null;
            }

            $categoryImagePath = optional($posts->first())->featured_image_path;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_url' => $this->resolveImageUrl($categoryImagePath),
                'posts' => $posts->map(function (BlogPost $post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'image_url' => $this->resolveImageUrl($post->featured_image_path),
                        'published_at' => $this->formatPublishedAt($post),
                        'is_featured' => (bool) $post->is_featured,
                    ];
                }),
            ];
        })->filter()->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    private function formatPublishedAt(BlogPost $post): ?string
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

    public function postsBySlug(string $slug): JsonResponse
    {
        $category = BlogCategory::where('slug', $slug)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $paginator = BlogPost::query()
            ->select([
                'id',
                'category_id',
                'title',
                'slug',
                'content',
                'featured_image_path',
                'published_at',
                'created_at',
                'is_featured',
            ])
            ->where('status', BlogPost::STATUS_PUBLISHED)
            ->where('category_id', $category->id)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(10);

        $paginator->getCollection()->transform(function (BlogPost $post) {
            $timestamp = $post->published_at ?? $post->created_at;
            $isoDate = $timestamp
                ? $timestamp->timezone(config('app.timezone'))->toDateString()
                : null;

            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => Str::limit(strip_tags((string) $post->content), 160),
                'image_url' => $this->resolveImageUrl($post->featured_image_path),
                'published_at' => $isoDate,
                'published_at_readable' => $this->formatPublishedAt($post),
                'is_featured' => (bool) $post->is_featured,
            ];
        });

        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'total_posts' => $paginator->total(),
                'back_url' => url('/'),
            ],
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function latestBySlug(string $slug): JsonResponse
    {
        $blogPost = BlogPost::where('slug', $slug)->first();

        if (!$blogPost) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $excludeSlug = trim((string) request()->input('exclude'));

        $posts = BlogPost::query()
            ->select([
                'id',
                'category_id',
                'title',
                'slug',
                'content',
                'featured_image_path',
                'published_at',
                'created_at',
                'is_featured',
            ])
            ->where('status', BlogPost::STATUS_PUBLISHED)
            ->where('category_id', $blogPost->category_id)
            ->when($excludeSlug !== '', function ($query) use ($excludeSlug) {
                $query->where('slug', '!=', $excludeSlug);
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(function (BlogPost $post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => Str::limit(strip_tags((string) $post->content), 160),
                    'image_url' => $this->resolveImageUrl($post->featured_image_path),
                    'published_at' => $this->formatPublishedAt($post),
                    'is_featured' => (bool) $post->is_featured,
                ];
            });

        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'data' => $posts,
        ]);
    }

    public function excludeSlug(string $slug): JsonResponse
    {
        $categories = BlogCategory::query()
            ->where('slug', '!=', $slug)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ])
            ->map(fn (BlogCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

        return response()->json([
            'data' => $categories,
        ]);
    }
}
