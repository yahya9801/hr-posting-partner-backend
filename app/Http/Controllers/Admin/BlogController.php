<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $posts = BlogPost::with('category')
            ->latest('published_at')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.blogs.index', [
            'posts' => $posts,
            'perPage' => $perPage,
            'perPageOptions' => [10, 25, 50],
        ]);
    }

    public function create(): View
    {
        return view('admin.blogs.create', [
            'statuses' => BlogPost::STATUSES,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRequest($request);

        $status = $data['status'] ?? BlogPost::STATUS_DRAFT;
        $featuredImagePath = $this->handleImageUpload($request);
        $publishedAt = $this->resolvePublishedAt(array_merge($data, ['status' => $status]));
        $categoryId = $this->resolveCategoryId($data['category_id'] ?? null);

        $post = BlogPost::create([
            'title' => $data['title'],
            'slug' => empty($data['slug'])
                ? BlogPost::generateUniqueSlug($data['title'])
                : $data['slug'],
            'content' => $data['content'],
            'featured_image_path' => $featuredImagePath,
            'status' => $status,
            'author_id' => Auth::id(),
            'category_id' => $categoryId,
            'published_at' => $publishedAt,
        ]);

        return redirect()
            ->route('admin.blogs.edit', $post)
            ->with('success', 'Blog post created successfully.');
    }

    public function show(BlogPost $blog): View
    {
        return view('admin.blogs.show', ['post' => $blog->loadMissing(['category', 'author'])]);
    }

    public function edit(BlogPost $blog): View
    {
        return view('admin.blogs.edit', [
            'post' => $blog->loadMissing('category'),
            'statuses' => BlogPost::STATUSES,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, BlogPost $blog): RedirectResponse
    {
        $data = $this->validateRequest($request, $blog->id);

        $status = $data['status'] ?? BlogPost::STATUS_DRAFT;
        $slug = empty($data['slug'])
            ? ($blog->title === $data['title']
                ? $blog->slug
                : BlogPost::generateUniqueSlug($data['title']))
            : $data['slug'];

        $featuredImagePath = $this->handleImageUpload($request, $blog->featured_image_path);
        $categoryId = $this->resolveCategoryId($data['category_id'] ?? null);

        $blog->update([
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'featured_image_path' => $featuredImagePath,
            'status' => $status,
            'category_id' => $categoryId,
            'published_at' => $this->resolvePublishedAt(array_merge($data, ['status' => $status]), $blog),
        ]);

        return redirect()
            ->route('admin.blogs.edit', $blog)
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $blog): RedirectResponse
    {
        if ($blog->featured_image_path && Storage::disk('public')->exists($blog->featured_image_path)) {
            Storage::disk('public')->delete($blog->featured_image_path);
        }

        $blog->delete();

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog post deleted successfully.');
    }

    private function validateRequest(Request $request, ?int $postId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:150', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:blog_posts,slug,'.($postId ?? 'NULL').',id'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'status' => ['required', Rule::in(BlogPost::STATUSES)],
            'published_at' => ['nullable', 'date'],
            'category_id' => ['nullable', 'string', 'max:80'],
        ], [
            'slug.regex' => 'The slug must use kebab-case (lowercase letters, numbers, and hyphens).',
        ]);
    }

    private function resolvePublishedAt(array $data, ?BlogPost $existing = null): ?Carbon
    {
        $status = $data['status'] ?? BlogPost::STATUS_DRAFT;
        $publishedAt = $data['published_at'] ?? null;

        if ($status === BlogPost::STATUS_PUBLISHED) {
            if ($publishedAt) {
                return Carbon::parse($publishedAt)->startOfDay();
            }

            if ($existing && $existing->published_at) {
                return $existing->published_at;
            }

            return Carbon::today();
        }

        if ($status === BlogPost::STATUS_ARCHIVED) {
            if ($publishedAt) {
                return Carbon::parse($publishedAt)->startOfDay();
            }

            return $existing?->published_at;
        }

        return null;
    }

    private function handleImageUpload(Request $request, ?string $existingPath = null): ?string
    {
        if (!$request->hasFile('featured_image')) {
            return $existingPath;
        }

        $path = $request->file('featured_image')->store('blog-images', 'public');

        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $path;
    }

    private function resolveCategoryId($categoryInput): ?int
    {
        if ($categoryInput === null) {
            return null;
        }

        $categoryInput = trim((string) $categoryInput);

        if ($categoryInput === '') {
            return null;
        }

        if (ctype_digit($categoryInput)) {
            $existingById = BlogCategory::find((int) $categoryInput);
            if ($existingById) {
                return $existingById->id;
            }
        }

        $normalized = Str::lower($categoryInput);

        $existing = BlogCategory::whereRaw('LOWER(name) = ?', [$normalized])->first();
        if ($existing) {
            return $existing->id;
        }

        $category = BlogCategory::create([
            'name' => $categoryInput,
        ]);

        return $category->id;
    }
}
