<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BlogCategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = BlogCategory::query()
            ->whereHas('posts', fn ($q) =>
                $q->where('status', BlogPost::STATUS_PUBLISHED)
            )
            ->with([
                'posts' => function ($q) {
                    // Build a ranked subquery over blog_posts
                    $ranked = DB::table('blog_posts as p')
                        ->selectRaw('
                            p.id,
                            p.category_id,
                            p.title,
                            p.slug,
                            p.featured_image_path,
                            p.published_at,
                            p.created_at,
                            ROW_NUMBER() OVER (
                            PARTITION BY p.category_id
                            ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC
                            ) as rn
                        ')
                        ->where('p.status', BlogPost::STATUS_PUBLISHED);

                    // Replace the relation table with the ranked subquery and keep rn <= 3
                    $q->fromSub($ranked, 'bp')
                    ->where('bp.rn', '<=', 3)
                    ->orderByRaw('COALESCE(bp.published_at, bp.created_at) DESC, bp.id DESC');
                },
            ])
            ->orderBy('name')
            ->get(['id','name','slug']);

        $data = $categories->map(function (BlogCategory $category) {
            $posts = $category->posts->values();
            $categoryImagePath = optional($posts->first())->featured_image_path;

            return [
                'id'        => $category->id,
                'name'      => $category->name,
                'slug'      => $category->slug,
                'image_url' => $this->resolveImageUrl($categoryImagePath),
                'posts'     => $posts->map(function ($post) {
                    return [
                        'id'           => $post->id,
                        'title'        => $post->title,
                        'slug'         => $post->slug,
                        'image_url'    => $this->resolveImageUrl($post->featured_image_path),
                        'published_at' => $this->formatPublishedAt($post),
                    ];
                }),
            ];
        });

        return response()->json(['data' => $data]);
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
