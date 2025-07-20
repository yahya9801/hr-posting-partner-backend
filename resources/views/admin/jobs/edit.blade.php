@extends('layouts.app')
@php
    use Carbon\Carbon;
@endphp

@section('content')
    <h2 class="text-xl font-bold mb-4">Edit Job</h2>

    <form method="POST" action="{{ route('admin.jobs.update', $job) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-semibold mb-1">Job Title</label>
            <input type="text" name="job_title" class="w-full border p-2 rounded" value="{{ old('job_title', $job->job_title) }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Slug (optional)</label>
            <input type="text" name="slug" disabled class="w-full border p-2 rounded" value="{{ old('slug', $job->slug) }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Short Description</label>
            <textarea name="short_description" rows="3" class="w-full border p-2 rounded">{{ old('short_description', $job->short_description) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Description</label>
            <textarea id="description" name="description" class="w-full border p-2 rounded">{{ old('description', $job->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Image</label>
            <input type="file" name="image">
            @if ($job->image_path)
                <img src="{{ asset('storage/' . $job->image_path) }}" class="mt-2 w-32 h-32 object-cover rounded" />
            @endif
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Posted At</label>
            <input type="date" name="posted_at" class="w-full border p-2 rounded"
                value="{{ old('posted_at', Carbon::parse($job->posted_at)->toDateString()) }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Location</label>
            <select name="locations[]" id="location-select2" multiple class="w-full border p-2 rounded">
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}"
                        {{ in_array($location->id, $job->locations->pluck('id')->toArray()) ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Roles</label>
            <select name="roles[]" id="role-select" multiple class="w-full border p-2 rounded">
            @foreach ($roles as $role)
                    <option value="{{ $role->id }}"
                        {{ in_array($role->id, $job->roles->pluck('id')->toArray()) ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 mt-1">You can also type to add a new role.</p>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update Job</button>
    </form>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#location-select2').select2({
            tags: true,
            placeholder: "Select or type a new location",
            width: '100%',
            ajax: {
                url: "{{ url('/api/locations') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            },
            createTag: function (params) {
                let term = $.trim(params.term);
                if (term === '') return null;
                return {
                    id: term,
                    text: term,
                    newTag: true // add flag
                };
            }
        });


        $('#role-select').select2({
            tags: true,
            placeholder: "Select or type a new role",
            width: '100%',
            ajax: {
                url: "{{ url('/api/roles') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            },
            createTag: function (params) {
                let term = $.trim(params.term);
                if (term === '') return null;
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            }
        });
    });
</script>
@endpush

@push('scripts')
<script src="https://cdn.tiny.cloud/1/ebdkodarptpuz9vfq6672fcp1iclck2tihn0u0mkfn4u7mxu/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        tinymce.init({
            selector: '#postBody',
            // Additional TinyMCE configurations as needed
            setup: function (editor) {
                editor.on('change', function () {
                    // Update the textarea content and trigger a change event
                    // so that the form validation knows the field has been filled
                    editor.save();
                    $('#postBody').trigger('change');
                });
            },
            plugins: 'advcode',
            toolbar: 'code',
            paste_data_images: true,
            selector: '#description', // change this value according to your HTML
            advcode_inline: true,
            valid_elements: 'p[style],h1,h2,h3,h4,h5,h6,strong/b,em/i,ul,ol,li,img[src],a[href]'
        });
    });
</script>


@endpush
