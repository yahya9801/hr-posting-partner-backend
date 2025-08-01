@extends('layouts.app')

@php
    $locationIds = old('locations', []);
    $preselectedLocations = \App\Models\Location::whereIn('id', $locationIds)->get();
@endphp

@php
    $roleIds = old('roles', []);
    $preselectedRoles = \App\Models\Role::whereIn('id', $roleIds)->get();
@endphp

@php
    $experienceIds = old('experience', []);
    $preselectedExperiences = \App\Models\Experience::whereIn('id', $experienceIds)->get();
@endphp

@section('content')
    <div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Create New Job</h2>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-800 p-4 rounded mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" id="job-create-form" action="{{ route('admin.jobs.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Job Title --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                <input type="text" name="job_title" value="{{ old('job_title') }}"
                    class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring focus:border-blue-400" required>
                @error('job_title')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Location --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location(s)</label>
                <select name="locations[]" id="location-select" multiple class="w-full border border-gray-300 p-2 rounded">
                    @foreach ($preselectedLocations as $loc)
                        <option value="{{ $loc->id }}" selected>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Roles --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role(s)</label>
                <select name="roles[]" id="role-select" multiple class="w-full border border-gray-300 p-2 rounded">
                    @foreach ($preselectedRoles as $role)
                        <option value="{{ $role->id }}" selected>{{ $role->name }}</option>
                    @endforeach
                </select>

            </div>

            {{-- Experience --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Experience</label>
                <select name="experience[]" id="experience-select" multiple class="w-full border border-gray-300 p-2 rounded">
                    @if(old('experience'))
                    @foreach ($preselectedExperiences as $exp)
                        <option value="{{ $exp->id }}" selected>{{ $exp->name }}</option>
                    @endforeach
                    @endif
                </select>

            </div>

            {{-- Short Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                <textarea name="short_description" rows="4" class="w-full border border-gray-300 p-2 rounded resize-none">{{ old('short_description') }}</textarea>
            </div>

            {{-- Full Description --}}
            <div class="mb-3">
                <label class="form-label block text-sm font-medium text-gray-700 mb-1">Description</label>
                <x-wysiwyg-editor 
                    name="description" 
                    :value="old('description')" 
                    id="description-editor" 
                />
                @error('description')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Images --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Images</label>
                <input type="file" name="images[]" multiple accept="image/*"
                    class="w-full text-sm border border-gray-300 rounded px-3 py-2">
                <small class="text-gray-500">You can upload multiple images.</small>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Posted At</label>
                    <input type="date" name="posted_at" value="{{ old('posted_at', now()->toDateString()) }}"
                        class="w-full border border-gray-300 p-2 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiry At</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', now()->addWeeks(2)->toDateString()) }}"
                        class="w-full border border-gray-300 p-2 rounded">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded shadow">
                    Create Job
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // Sync contenteditable to hidden textarea before submit
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('job-create-form');
        const editor = document.getElementById('description-editor');
        const hiddenInput = document.getElementById('hidden-description-editor');

        if (form && editor && hiddenInput) {
            form.addEventListener('submit', () => {
                hiddenInput.value = editor.innerHTML;
            });
        }
    });
</script>

<script>
    // Select2 initialization
    $(document).ready(function () {
        $('#location-select').select2({
            tags: true,
            placeholder: "Select or type a new location",
            width: '100%',
            ajax: {
                url: "{{ url('/api/locations') }}",
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data }),
                cache: true
            },
            createTag: params => {
                const term = $.trim(params.term);
                return term ? { id: term, text: term, newTag: true } : null;
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
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data }),
                cache: true
            },
            createTag: params => {
                const term = $.trim(params.term);
                return term ? { id: term, text: term, newTag: true } : null;
            }
        });

        $('#experience-select').select2({
            tags: true,
            placeholder: "Select or type a new experience level",
            width: '100%',
            ajax: {
                url: "{{ url('/api/experience') }}",
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data }),
                cache: true
            },
            createTag: params => {
                const term = $.trim(params.term);
                return term ? { id: term, text: term, newTag: true } : null;
            }
        });
    });
</script>
@endpush
