@extends('layouts.app')

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

        <div id="form-errors" class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-6 hidden">
            <ul id="form-errors-list" class="list-disc list-inside text-sm space-y-1"></ul>
        </div>

        <form method="POST" id='job-create-form' action="{{ route('admin.jobs.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Job Title --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                <input type="text" value="{{ old('job_title') }}"name="job_title" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring focus:border-blue-400" required>
            </div>

            {{-- Locations --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location(s)</label>
                <select name="locations[]" id="location-select" multiple class="w-full border border-gray-300 p-2 rounded"></select>
                <p class="text-xs text-gray-500 mt-1">Type to add a new location.</p>
            </div>

            {{-- Roles --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role(s)</label>
                <select name="roles[]" id="role-select" multiple class="w-full border border-gray-300 p-2 rounded"></select>
                <p class="text-xs text-gray-500 mt-1">Type to add a new role.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Experience</label>
                <select name="experience[]" id="experience-select" multiple class="w-full border border-gray-300 p-2 rounded"></select>
                <p class="text-xs text-gray-500 mt-1">Type to add a new experience.</p>
            </div>

            <!-- {{-- Experience --}}
            <div>
                <label for="experience" class="block text-sm font-medium text-gray-700 mb-1">Experience</label>
                <select name="experience" id="experience" class="w-full border border-gray-300 p-2 rounded">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                    <option value="Less than 1 year">Less than 1 year</option>
                </select>
                @error('experience')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div> -->

            {{-- Short Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                <textarea name="short_description" rows="4" class="w-full border border-gray-300 p-2 rounded resize-none"></textarea>
            </div>

            {{-- Full Description --}}
            <div class="mb-3">
                <label for="description" class="form-label">description</label>
                <x-wysiwyg-editor name="description" :value="old('description')" id="description-editor" />
            </div>

            {{-- Image --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Images</label>
                <input
                    type="file"
                    name="images[]"
                    multiple
                    accept="image/*"
                    class="w-full text-sm border border-gray-300 rounded px-3 py-2"
                >
                <small class="text-gray-500">You can upload multiple images.</small>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Posted At</label>
                    <input type="date" name="posted_at" value="{{ now()->toDateString() }}" class="w-full border border-gray-300 p-2 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiry At</label>
                    <input type="date" name="expiry_date" value="{{ now()->addWeeks(2)->toDateString() }}" class="w-full border border-gray-300 p-2 rounded">
                </div>
            </div>

            {{-- Submit --}}
            <div class="pt-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded shadow">
                    Create Job
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <!-- <script src="https://cdn.tiny.cloud/1/ebdkodarptpuz9vfq6672fcp1iclck2tihn0u0mkfn4u7mxu/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script> -->
    <script src="{{ asset('tinymce/tinymce.min.js') }}"></script>
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
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data }),
                    cache: true
                },
                createTag: params => {
                    let term = $.trim(params.term);
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
                    let term = $.trim(params.term);
                    return term ? { id: term, text: term, newTag: true } : null;
                }
            });

            $('#experience-select').select2({
                tags: true,
                placeholder: "Select or type a new Experience",
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
                    let term = $.trim(params.term);
                    return term ? { id: term, text: term, newTag: true } : null;
                }
            });
        });


        // document.querySelector('form')?.addEventListener('submit', function () {
        //     document.getElementById('editor-content').value = document.querySelector('.editor').innerHTML;
        
        //     console.log(document.querySelector('.editor').innerHTML)
        // });
        // document.addEventListener('DOMContentLoaded', function () {
        //     tinymce.init({
        //         selector: '#description',
        //         plugins: 'advcode',
        //         toolbar: 'code',
        //         paste_data_images: true,
        //         advcode_inline: true,
        //         valid_elements: 'p[style],h1,h2,h3,h4,h5,h6,strong/b,em/i,ul,ol,li,img[src],a[href]',
        //         setup: function (editor) {
        //             editor.on('change', function () {
        //                 editor.save();
        //                 $('#description').trigger('change');
        //             });
        //         }
        //     });
        // });
    </script>
        <script>
function handleEditorFormSubmit(formId, editorId, textareaId) {
  const form = document.getElementById('job-create-form');
  const editor = document.getElementById('description-editor');
  const hiddenTextarea = document.getElementById('hidden-description-editor');

  if (!form || !editor || !hiddenTextarea) {
    console.warn('Missing form, editor, or textarea.');
    return;
  }

  form.addEventListener('submit', function () {
    hiddenTextarea.value = editor.getHTML();
    console.log('Editor HTML saved:', hiddenTextarea.value);
  });
}

document.addEventListener('DOMContentLoaded', function () {
  handleEditorFormSubmit('job-create-form', 'description-editor', 'hidden-description-editor');
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('job-create-form');

    form.addEventListener('submit', function (e) {
        const errors = [];

        // Clear previous errors
        document.getElementById('form-errors').classList.add('hidden');
        document.getElementById('form-errors-list').innerHTML = '';

        // Job Title
        const jobTitle = document.querySelector('[name="job_title"]').value.trim();
        if (!jobTitle) {
            errors.push("Job title is required.");
        }

        // Locations
        const locations = $('#location-select').val();
        if (!locations || locations.length === 0) {
            errors.push("Please select at least one location.");
        }

        // Roles
        const roles = $('#role-select').val();
        if (!roles || roles.length === 0) {
            errors.push("Please select at least one role.");
        }

        // Experience
        const experience = $('#experience-select').val();
        if (!experience || experience.length === 0) {
            errors.push("Please select at least one experience level.");
        }

        // Set WYSIWYG content
        const editorHtml = document.querySelector('.editor')?.innerHTML;
        document.getElementById('editor-content').value = editorHtml;

        // Optional: Validate WYSIWYG content
        if (!editorHtml || editorHtml.trim() === '' || editorHtml.trim() === '<br>') {
            errors.push("Job description cannot be empty.");
        }

        // If there are errors, prevent submission and display them
        if (errors.length > 0) {
            e.preventDefault();

            const ul = document.getElementById('form-errors-list');
            errors.forEach(err => {
                const li = document.createElement('li');
                li.textContent = err;
                ul.appendChild(li);
            });

            document.getElementById('form-errors').classList.remove('hidden');
        }
    });
});
</script>

@endpush

