@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Blog Post</h2>
            <a href="{{ route('admin.blogs.show', $post) }}" class="text-sm text-blue-600 hover:underline">View post</a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-800 p-4 rounded mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" id="blog-edit-form" action="{{ route('admin.blogs.update', $post) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @include('admin.blogs.partials.form', [
                'post' => $post,
                'statuses' => $statuses,
                'categories' => $categories,
            ])

            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('admin.blogs.index') }}" class="text-sm text-gray-600 hover:underline">Back to posts</a>
                <div class="space-x-3">
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded shadow">Update Post</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function setupBlogCategorySelect(selector) {
        if (typeof window.$ === 'undefined' || !window.$.fn || !window.$.fn.select2) {
            return;
        }

        window.$(selector).select2({
            tags: true,
            width: '100%',
            allowClear: true,
            placeholder: 'Select or type a category',
            createTag: params => {
                const term = window.$.trim(params.term);
                return term ? { id: term, text: term, newTag: true } : null;
            }
        });
    }

    function bindWysiwygSubmission(form) {
        if (!form) {
            return;
        }

        const wrapper = form.querySelector('.wysiwyg-wrapper');
        if (!wrapper) {
            return;
        }

        const editor = wrapper.querySelector('.editor');
        const hiddenInput = wrapper.querySelector('textarea[id^="hidden-"]');

        if (editor && hiddenInput) {
            form.addEventListener('submit', () => {
                hiddenInput.value = editor.innerHTML;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('blog-edit-form');
        bindWysiwygSubmission(form);
        setupBlogCategorySelect('#category_id');
    });
</script>
@endpush
