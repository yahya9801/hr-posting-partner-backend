@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Job</h2>

    <form method="POST" action="{{ route('admin.jobs.update', $job) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Job Title --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
            <input type="text" name="job_title" class="w-full border p-2 rounded" value="{{ old('job_title', $job->job_title) }}" required>
        </div>

        {{-- Slug --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug (readonly)</label>
            <input type="text" name="slug" disabled class="w-full border p-2 rounded bg-gray-100" value="{{ old('slug', $job->slug) }}">
        </div>

        {{-- Short Description --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
            <textarea name="short_description" rows="3" class="w-full border p-2 rounded resize-none">{{ old('short_description', $job->short_description) }}</textarea>
        </div>

        {{-- Full Description --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
            <textarea id="description" name="description" rows="6" class="w-full border p-2 rounded resize-none">{{ old('description', $job->description) }}</textarea>
        </div>

        {{-- Image Upload --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
            <input type="file" name="image" class="text-sm">
            @if ($job->image_path)
                <img src="{{ asset('storage/' . $job->image_path) }}" class="mt-2 w-32 h-32 object-cover rounded" />
            @endif
        </div>

        {{-- Posted & Expiry Dates --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Posted At</label>
                <input type="date" name="posted_at" class="w-full border p-2 rounded"
                    value="{{ old('posted_at', Carbon::parse($job->posted_at)->toDateString()) }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry At</label>
                <input type="date" name="expiry_date" class="w-full border p-2 rounded"
                    value="{{ old('expiry_date', Carbon::parse($job->expiry_date)->toDateString()) }}">
            </div>
        </div>

        {{-- Locations --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location(s)</label>
            <select name="locations[]" id="location-select2" multiple class="w-full border p-2 rounded">
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" {{ in_array($location->id, $job->locations->pluck('id')->toArray()) ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Roles --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role(s)</label>
            <select name="roles[]" id="role-select" multiple class="w-full border p-2 rounded">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ in_array($role->id, $job->roles->pluck('id')->toArray()) ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">You can also type to add a new role.</p>
        </div>

        {{-- Submit --}}
        <div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">Update Job</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.tiny.cloud/1/ebdkodarptpuz9vfq6672fcp1iclck2tihn0u0mkfn4u7mxu/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        $(document).ready(function () {
            function setupSelect2(id, url) {
                $(id).select2({
                    tags: true,
                    placeholder: "Select or type to add new",
                    width: '100%',
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: data => ({ results: data }),
                        cache: true
                    },
                    createTag: params => {
                        let term = $.trim(params.term);
                        return term ? { id: term, text: term, newTag: true } : null;
                    }
                });
            }

            setupSelect2('#location-select2', "{{ url('/api/locations') }}");
            setupSelect2('#role-select', "{{ url('/api/roles') }}");

            tinymce.init({
                selector: '#description',
                plugins: 'advcode',
                toolbar: 'code',
                paste_data_images: true,
                advcode_inline: true,
                valid_elements: 'p[style],h1,h2,h3,h4,h5,h6,strong/b,em/i,ul,ol,li,img[src],a[href]',
                setup: editor => {
                    editor.on('change', () => {
                        editor.save();
                        $('#description').trigger('change');
                    });
                }
            });
        });
    </script>
@endpush
