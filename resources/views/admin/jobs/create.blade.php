@extends('layouts.app')


@section('content')
    <h2 class="text-xl font-bold mb-4">Create New Job</h2>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form method="POST" action="{{ route('admin.jobs.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div>
            <label class="block font-semibold mb-1">Job Title</label>
            <input type="text" name="job_title" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label class="block font-semibold mb-1">Location</label>
            <select name="locations[]" id="location-select" multiple class="w-full border p-2 rounded">
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 mt-1">You can also type to add a new location.</p>
        </div>

        <div>
            <label class="block font-semibold mb-1">Roles</label>
            <select name="roles[]" id="role-select" multiple class="w-full border p-2 rounded">
                {{-- Select2 will handle options via AJAX --}}
            </select>
            <p class="text-sm text-gray-500 mt-1">You can also type to add a new role.</p>
        </div>

        <div>
            <label class="block font-semibold mb-1">Description</label>
            <textarea id="description" name="description" class="w-full border p-2 rounded" rows="6"></textarea>
        </div>

        <div>
            <label class="block font-semibold mb-1">Image</label>
            <input type="file" name="image" class="block">
        </div>

        <div>
            <label class="block font-semibold mb-1">Posted At</label>
            <input type="date" name="posted_at" class="w-full border p-2 rounded" value="{{ now()->toDateString() }}">
        </div>

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Create Job</button>
    </form>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#location-select').select2({
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
            plugins: 'paste advcode',
            toolbar: 'paste code',
            paste_data_images: true,
            selector: 'textarea', // change this value according to your HTML
            advcode_inline: true,
            valid_elements: 'p[style],h1,h2,h3,h4,h5,h6,strong/b,em/i,ul,ol,li,img[src],a[href]'
        });
    });
</script>


@endpush



