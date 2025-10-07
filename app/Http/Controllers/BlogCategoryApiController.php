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
                    ])
                        ->where('status', BlogPost::STATUS_PUBLISHED)
                        ->latest('published_at')
                        ->latest()
                        ->take(3);
                },
            ])
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ]);

        $data = $categories->map(function (BlogCategory $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'posts' => $category->posts->map(function (BlogPost $post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'image_url' => $this->resolveImageUrl($post->featured_image_path),
                        'published_at' => optional($post->published_at)->toIso8601String(),
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
