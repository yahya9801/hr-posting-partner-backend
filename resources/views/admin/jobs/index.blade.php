@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold mb-4">All Jobs</h2>

    <a href="{{ route('admin.jobs.create') }}" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded">+ New Job</a>

    <form method="GET" action="{{ route('admin.jobs.index') }}" class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center">
        <input
            type="text"
            name="search"
            value="{{ old('search', $search) }}"
            placeholder="Search by title, slug, or description"
            class="w-full rounded border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:flex-1"
        >
        <div class="flex gap-2">
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Search</button>
            @if ($search)
                <a href="{{ route('admin.jobs.index') }}" class="rounded border border-gray-300 px-4 py-2 text-gray-700">Clear</a>
            @endif
        </div>
    </form>

    @if ($jobs->count())
        <table class="w-full bg-white shadow rounded">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-3">Job Title</th>
                    <th class="p-3">Slug</th>
                    <th class="p-3">Posted At</th>
                    <th class="p-3">Expiry Date</th>
                    <th></th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jobs as $job)
                <tr class="border-b">
                    <td class="p-3">{{ $job->job_title }}</td>
                    <td class="p-3">
                        <a
                            target="_blank"
                            class="text-blue-600 underline break-words"
                            href="https://hrpostingpartner.com/classified-jobs/{{ $job->slug }}"
                        >
                            https://hrpostingpartner.com/classified-jobs/{{ $job->slug }}
                        </a>
                    </td>

                    <td class="p-3">{{ \Carbon\Carbon::parse($job->posted_at)->format('Y-m-d') }}</td>
                    <td class="p-3">
                        {{ $job->expiry_date ? \Carbon\Carbon::parse($job->expiry_date)->format('Y-m-d') : 'N/A' }}
                    </td>
                    <td><a href="{{ route('admin.jobs.show', $job) }}" class="text-blue-600 font-medium hover:underline">View Details</a></td>
                    <td class="p-3 space-x-2">
                        <a href="{{ route('admin.jobs.edit', $job) }}" class="text-blue-600">Edit</a>
                        <form action="{{ route('admin.jobs.destroy', $job) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500" onclick="return confirm('Delete this job?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6">
            {{ $jobs->links('pagination::tailwind') }}
        </div>
    @else
        <p>No jobs posted yet.</p>
    @endif
@endsection
