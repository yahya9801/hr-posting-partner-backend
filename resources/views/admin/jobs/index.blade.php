@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold mb-4">All Jobs</h2>

    <a href="{{ route('admin.jobs.create') }}" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded">+ New Job</a>

    @if ($jobs->count())
        <table class="w-full bg-white shadow rounded">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-3">Job Title</th>
                    <th class="p-3">Posted At</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jobs as $job)
                <tr class="border-b">
                    <td class="p-3">{{ $job->job_title }}</td>
                    <td class="p-3">{{ \Carbon\Carbon::parse($job->posted_at)->format('Y-m-d') }}</td>
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
