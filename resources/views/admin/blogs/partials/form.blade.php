@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
    $post = $post ?? null;

    $publishedAtValue = old('published_at');
    if (is_string($publishedAtValue) && str_contains($publishedAtValue, 'T')) {
        $publishedAtValue = explode('T', $publishedAtValue, 2)[0];
    }
    if (!$publishedAtValue && $post?->published_at) {
        $publishedAtValue = $post->published_at->format('Y-m-d');
    }

    $statusValue = old('status', $post->status ?? \App\Models\BlogPost::STATUS_DRAFT);
    $categoryValue = old('category_id', $post->category_id ?? '');

    $featuredOldValue = old('is_featured');
    if ($featuredOldValue === null) {
        $isFeaturedValue = (bool) ($post->is_featured ?? false);
    } else {
        $isFeaturedValue = filter_var($featuredOldValue, FILTER_VALIDATE_BOOLEAN);
    }

    $baseInputClasses = 'w-full rounded-md px-3 py-2 border shadow-sm bg-white focus:outline-none transition-colors';
    $inputClasses = fn (string $field) => $baseInputClasses
        . ($errors->has($field)
            ? ' border-red-500 focus:border-red-500 focus:ring-2 focus:ring-red-200'
            : ' border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100');
@endphp

<div class="space-y-6">
    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="title">Title</label>
        <input
            id="title"
            type="text"
            name="title"
            maxlength="100"
            value="{{ old('title', $post->title ?? '') }}"
            class="{{ $inputClasses('title') }}"
            required
        >
        @error('title')
            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="slug">Slug (kebab-case)</label>
            <input
                id="slug"
                type="text"
                name="slug"
                maxlength="150"
                value="{{ old('slug', $post->slug ?? '') }}"
                placeholder="Leave blank to auto-generate"
                class="{{ $inputClasses('slug') }}"
            >
            @error('slug')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="status">Status</label>
            <select id="status" name="status" class="{{ $inputClasses('status') }}">
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption }}" {{ $statusValue === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst(strtolower($statusOption)) }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="is_featured">Featured Post</label>
            <input type="hidden" name="is_featured" value="0">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input
                    id="is_featured"
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    @checked($isFeaturedValue)
                >
                <span>Feature this post on the public site</span>
            </label>
            <p class="text-xs text-gray-500 mt-2">Featured posts can be highlighted on the front end.</p>
            @error('is_featured')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="category_id">Category</label>
        <select id="category_id" name="category_id" class="{{ $inputClasses('category_id') }} category-select">
            <option value="">Select or create a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ (string) $categoryValue === (string) $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-2">Start typing to add a new category if it does not exist.</p>
        @error('category_id')
            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
        <label class="block text-sm font-semibold text-gray-700 mb-3" for="content">Content</label>
        <x-wysiwyg-editor
            name="content"
            :value="old('content', $post->content ?? '')"
            id="blog-content-editor"
        />
        @error('content')
            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="featured_image">Featured Image</label>
        <input
            id="featured_image"
            type="file"
            name="featured_image"
            accept="image/*"
            class="{{ $inputClasses('featured_image') }}"
        >
        <p class="text-xs text-gray-500 mt-2">Upload a JPG or PNG (max 5 MB).</p>
        @error('featured_image')
            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
        @enderror

        @if (!empty($post?->featured_image_path))
            <div class="mt-4">
                <p class="text-sm text-gray-600 mb-2">Current image:</p>
                <img src="{{ asset('storage/' . $post->featured_image_path) }}" alt="{{ $post->title }}" class="max-h-48 rounded border border-gray-200 shadow-sm">
            </div>
        @endif
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="published_at">Publish Date</label>
        <input
            id="published_at"
            type="date"
            name="published_at"
            value="{{ $publishedAtValue }}"
            class="{{ $inputClasses('published_at') }}"
        >
        @error('published_at')
            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
        @enderror
    </div>
</div>
