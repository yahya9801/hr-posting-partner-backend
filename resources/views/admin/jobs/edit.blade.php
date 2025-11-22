@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Job</h2>

    <form method="POST" id="job-edit-form" action="{{ route('admin.jobs.update', $job) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Job Title --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
            <input type="text" name="job_title" class="w-full border p-2 rounded" value="{{ old('job_title', $job->job_title) }}" required>
            @error('job_title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug (readonly)</label>
            <input type="text" name="slug" disabled class="w-full border p-2 rounded bg-gray-100" value="{{ old('slug', $job->slug) }}">
        </div>

        {{-- Locations --}}
        @php $oldLocations = old('locations', $job->locations->pluck('id')->toArray()); @endphp
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location(s)</label>
            <select name="locations[]" id="location-select2" multiple class="w-full border p-2 rounded">
                @foreach ($locations as $location)
                <option value="{{ $location->id }}" {{ in_array($location->id, $oldLocations) ? 'selected' : '' }}>
                    {{ $location->name }}
                </option>
                @endforeach
                @foreach ($oldLocations as $id)
                @if (!in_array($id, $locations->pluck('id')->toArray()))
                <option value="{{ $id }}" selected>{{ $id }}</option>
                @endif
                @endforeach
            </select>
        </div>

        {{-- Roles --}}
        @php $oldRoles = old('roles', $job->roles->pluck('id')->toArray()); @endphp
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role(s)</label>
            <select name="roles[]" id="role-select" multiple class="w-full border p-2 rounded">
                @foreach ($roles as $role)
                <option value="{{ $role->id }}" {{ in_array($role->id, $oldRoles) ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
                @endforeach
                @foreach ($oldRoles as $id)
                @if (!in_array($id, $roles->pluck('id')->toArray()))
                <option value="{{ $id }}" selected>{{ $id }}</option>
                @endif
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">You can also type to add a new role.</p>
        </div>

        {{-- Experience --}}
        @php $oldExp = old('experience', $job->experiences->pluck('id')->toArray()); @endphp
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Experience</label>
            <select name="experience[]" id="experience-select" multiple class="w-full border border-gray-300 p-2 rounded">
                @foreach ($experiences as $experience)
                <option value="{{ $experience->id }}" {{ in_array($experience->id, $oldExp) ? 'selected' : '' }}>
                    {{ $experience->name }}
                </option>
                @endforeach
                @foreach ($oldExp as $id)
                @if (!in_array($id, $experiences->pluck('id')->toArray()))
                <option value="{{ $id }}" selected>{{ $id }}</option>
                @endif
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Type to add a new experience.</p>
        </div>

        {{-- Company --}}
        @php $oldCompanies = old('companies', $job->companies->pluck('id')->toArray()); @endphp
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
            <select name="companies[]" id="company-select" multiple class="w-full border border-gray-300 p-2 rounded">
                @foreach ($companies as $company)
                <option value="{{ $company->id }}" {{ in_array($company->id, $oldCompanies) ? 'selected' : '' }}>
                    {{ $company->name }}
                </option>
                @endforeach
                @foreach ($oldCompanies as $id)
                @if (!in_array($id, $companies->pluck('id')->toArray()))
                <option value="{{ $id }}" selected>{{ $id }}</option>
                @endif
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Type to add a new company.</p>
        </div>

        {{-- Short Description --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
            <textarea name="short_description" rows="3" class="w-full border p-2 rounded resize-none">{{ old('short_description', $job->short_description) }}</textarea>
            @error('short_description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Full Description --}}
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <!-- <input type="hidden" id="hidden-description-editor" name="description" value="{{ old('description', $job->description) }}"> -->
            <x-wysiwyg-editor
                name="description"
                :value="old('description', $job->description)"
                id="description-editor" />
            @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Image Upload --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Images</label>
            <input type="file" name="images[]" multiple accept="image/*" class="text-sm border rounded px-2 py-1">
            @if ($job->images)
            @foreach ($job->images as $image)
            <img src="{{ asset('storage/' . $image->image_path) }}" class="mt-2 w-32 h-32 object-cover rounded" />
            @endforeach
            @endif
        </div>

        {{-- Posted & Expiry Dates --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Posted At</label>
                <input type="date" name="posted_at" class="w-full border p-2 rounded" value="{{ old('posted_at', Carbon::parse($job->posted_at)->toDateString()) }}">
                @error('posted_at') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry At</label>
                <input type="date" name="expiry_date" class="w-full border p-2 rounded" value="{{ old('expiry_date', Carbon::parse($job->expiry_date)->toDateString()) }}">
                @error('expiry_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

       

        {{-- Submit --}}
        <div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">Update Job</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function setupSelect2(id, url) {
            $(id).select2({
                tags: true,
                placeholder: "Select or type to add new",
                width: '100%',
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: data
                    }),
                    cache: true
                },
                createTag: params => {
                    let term = $.trim(params.term);
                    return term ? {
                        id: term,
                        text: term,
                        newTag: true
                    } : null;
                }
            });
        }

        setupSelect2('#location-select2', "{{ url('/api/locations') }}");
        setupSelect2('#role-select', "{{ url('/api/roles') }}");
        setupSelect2('#experience-select', "{{ url('/api/experience') }}");
        setupSelect2('#company-select', "{{ url('/api/companies') }}");


        // tinymce.init({
        //     selector: '#description',
        //     plugins: 'advcode',
        //     toolbar: 'code',
        //     paste_data_images: true,
        //     advcode_inline: true,
        //     valid_elements: 'p[style],h1,h2,h3,h4,h5,h6,strong/b,em/i,ul,ol,li,img[src],a[href]',
        //     setup: editor => {
        //         editor.on('change', () => {
        //             editor.save();
        //             $('#description').trigger('change');
        //         });
        //     }
        // });




    });
</script>
<script>
    function handleEditorFormSubmit(formId, editorId, textareaId) {
        const form = document.getElementById('job-edit-form');
        const editor = document.getElementById('description-editor');
        const hiddenTextarea = document.getElementById('hidden-description-editor');
        
        if (!form || !editor || !hiddenTextarea) {
            console.warn('Missing form, editor, or textarea.');
        }

        form.addEventListener('submit', function() {
            hiddenTextarea.value = editor.getHTML();
            console.log('Editor HTML saved:', hiddenTextarea.value);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        handleEditorFormSubmit('job-edit-form', 'description-editor', 'hidden-description-editor');
    });
</script>@endpush
