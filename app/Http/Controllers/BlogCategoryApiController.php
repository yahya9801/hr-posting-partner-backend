<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BlogCategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = BlogCategory::query()
            ->whereHas('posts', function ($query) {
                $query->where('status', BlogPost::STATUS_PUBLISHED);
            })
            ->with([
                'posts' => function ($query) {
                    $query->select([
                        'id',
                        'category_id',
                        'title',
                        'slug',
                        'featured_image_path',
                        'published_at',
                        'created_at',
                    ])
                        ->where('status', BlogPost::STATUS_PUBLISHED)
                        ->orderByRaw('COALESCE(published_at) DESC');
                        // ⚠️ If Laravel 9+: this limits per category. If <9, see note below.
                        // ->limit(3);
                },
            ])
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ]);
        dd($categories);

        $data = $categories->map(function (BlogCategory $category) {
            $categoryImagePath = optional($category->posts->first())->featured_image_path;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_url' => $this->resolveImageUrl($categoryImagePath),
                'posts' => $category->posts->map(function (BlogPost $post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'image_url' => $this->resolveImageUrl($post->featured_image_path),
                        'published_at' => $this->formatPublishedAt($post),
                    ];
                }),
            ];
        });

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
