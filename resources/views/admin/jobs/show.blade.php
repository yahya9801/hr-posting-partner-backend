@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold mb-4">{{ $job->job_title }}</h1>

    <div class="text-sm text-gray-500 mb-2">
        Posted on {{ \Carbon\Carbon::parse($job->posted_at)->format('F d, Y') }}
        @if ($job->expiry_date)
            &middot; Expires on {{ \Carbon\Carbon::parse($job->expiry_date)->format('F d, Y') }}
        @endif
    </div>

    <div class="mb-4">
        <span>Locations:</span>
        @foreach ($job->locations as $location)
        <span class="inline-block bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded mr-1">
            {{ $location->name }}
        </span>
        @endforeach
    </div>

    <div class="mb-4">
        <span>Roles:</span>
        @foreach ($job->roles as $role)
        <span class="inline-block bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded mr-1">
            {{ $role->name }}
        </span>
        @endforeach
    </div>

    <div class="mb-4">
        <span>Experience:</span>
        @forelse ($job->experiences as $experience)
        <span class="inline-block bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded mr-1">
            {{ $experience->name }}
        </span>
        @empty
        <span class="text-gray-500 text-sm">N/A</span>
        @endforelse
    </div>

    <div class="mb-4">
        <span>Company:</span>
        @forelse ($job->companies as $company)
        <span class="inline-block bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded mr-1">
            {{ $company->name }}
        </span>
        @empty
        <span class="text-gray-500 text-sm">N/A</span>
        @endforelse
    </div>

    @if ($job->images && $job->images->count())
    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($job->images as $image)
        <img
            src="{{ asset('storage/' . $image->image_path) }}"
            alt="{{ $job->job_title }}"
            class="rounded shadow w-full max-h-96 object-cover">
        @endforeach
    </div>
    @endif

    <div class="prose max-w-none">
        {!! $job->description !!}
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.jobs.index') }}" class="text-blue-600 hover:underline">+ Back to Listings</a>
    </div>
</div>
@endsection
