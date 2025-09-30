@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">New Blog Post</h2>
            <a href="{{ route('admin.blogs.index') }}" class="text-sm text-blue-600 hover:underline">Back to posts</a>
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

        <form method="POST" id="blog-create-form" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @include('admin.blogs.partials.form', [
                'post' => null,
                'statuses' => $statuses,
                'categories' => $categories,
            ])

            <div class="flex items-center justify-end space-x-3 pt-4">
                <a href="{{ route('admin.blogs.index') }}" class="px-4 py-2 rounded border border-gray-300 text-gray-700">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded shadow">Save Post</button>
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
        const form = document.getElementById('blog-create-form');
        bindWysiwygSubmission(form);
        setupBlogCategorySelect('#category_id');
    });
</script>
@endpush
