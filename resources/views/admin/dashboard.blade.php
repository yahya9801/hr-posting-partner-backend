@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-semibold mb-4">Admin Dashboard</h2>

    <p class="mb-6">Welcome, {{ auth()->user()->name }}!</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 bg-white shadow rounded">
            <h3 class="font-semibold">View Jobs</h3>
            <a href="{{ route('admin.jobs.index') }}" class="text-blue-600 underline">Go to Job Listings</a>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <h3 class="font-semibold">Create Job</h3>
            <a href="{{ route('admin.jobs.create') }}" class="text-green-600 underline">Post a New Job</a>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <h3 class="font-semibold">View Blog Posts</h3>
            <a href="{{ route('admin.blogs.index') }}" class="text-blue-600 underline">Browse Blog Posts</a>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <h3 class="font-semibold">Create Blog Post</h3>
            <a href="{{ route('admin.blogs.create') }}" class="text-green-600 underline">Write a New Post</a>
        </div>
    </div>
@endsection
