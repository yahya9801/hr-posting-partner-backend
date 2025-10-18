@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Blog Posts</h2>
            <p class="text-sm text-gray-500">Manage published articles, drafts, and archived content.</p>
        </div>
        <a href="{{ route('admin.blogs.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded shadow">+ New Post</a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if ($posts->count())
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <p class="text-sm text-gray-500">
                Showing {{ $posts->firstItem() }} to {{ $posts->lastItem() }} of {{ $posts->total() }} posts
            </p>
            <form method="GET" class="flex items-center gap-2 text-sm text-gray-600">
                @foreach (request()->except('per_page') as $name => $value)
                    @foreach ((array) $value as $hiddenValue)
                        <input type="hidden" name="{{ $name }}" value="{{ $hiddenValue }}">
                    @endforeach
                @endforeach
                <label for="per-page" class="text-sm text-gray-600 whitespace-nowrap">Rows per page</label>
                <select id="per-page" name="per_page" class="border border-gray-300 rounded px-2 py-1 text-sm bg-white" onchange="this.form.submit()">
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($option === $perPage)>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Published</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($posts as $post)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $post->title }}</div>
                                <div class="text-sm text-gray-500">/{{ $post->slug }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ optional($post->category)->name ?? '--' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold {{ $post->status === 'PUBLISHED' ? 'bg-green-100 text-green-800' : ($post->status === 'ARCHIVED' ? 'bg-gray-200 text-gray-700' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst(strtolower($post->status)) }}
                                </span>
                                @if ($post->is_featured)
                                    <div class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                        Featured
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $post->published_at ? $post->published_at->format('Y-m-d') : '--' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $post->updated_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="{{ route('admin.blogs.show', $post) }}" class="text-sm text-blue-600 hover:underline">View</a>
                                <a href="{{ route('admin.blogs.edit', $post) }}" class="text-sm text-gray-700 hover:underline">Edit</a>
                                <form action="{{ route('admin.blogs.destroy', $post) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline" onclick="return confirm('Delete this post?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="mt-6">
                {{ $posts->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        @endif
    @else
        <div class="bg-white rounded shadow p-8 text-center text-gray-500">No blog posts yet.</div>
    @endif
@endsection

