@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">{{ $post->title }}</h2>
                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                    <span>Status:
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold {{ $post->status === 'PUBLISHED' ? 'bg-green-100 text-green-800' : ($post->status === 'ARCHIVED' ? 'bg-gray-200 text-gray-700' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst(strtolower($post->status)) }}
                        </span>
                    </span>
                    <span>Slug: <code>{{ $post->slug }}</code></span>
                    @if ($post->category)
                        <span>Category: <span class="font-semibold text-gray-700">{{ $post->category->name }}</span></span>
                    @endif
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.blogs.edit', $post) }}" class="px-4 py-2 bg-blue-600 text-white rounded">Edit</a>
                <a href="{{ route('admin.blogs.index') }}" class="text-sm text-blue-600 hover:underline">Back to list</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-6">
            <div>Published: {{ $post->published_at ? $post->published_at->format('M d, Y') : 'Not published' }}</div>
            <div>Last updated: {{ $post->updated_at->format('M d, Y') }}</div>
            <div>Author: {{ optional($post->author)->name ?? '-' }}</div>
        </div>

        @if ($post->featured_image_path)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Featured Image</h3>
                <img src="{{ asset('storage/' . $post->featured_image_path) }}" alt="{{ $post->title }}" class="max-h-64 rounded">
            </div>
        @endif

        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Content</h3>
            <div class="prose max-w-none text-gray-800 leading-relaxed">
                {!! $post->content !!}
            </div>
        </div>
    </div>
@endsection
