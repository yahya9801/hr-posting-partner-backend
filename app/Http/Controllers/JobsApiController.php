<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JobsApiController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10); // default 10
        $page = $request->input('page', 1);

        $query = Job::with('locations')
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
            });

        if ($request->filled('q')) {
            $searchTerm = $request->input('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('job_title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // ✅ Location filtering
        if ($request->filled('location')) {
            $locationIds = explode(',', $request->input('location'));
            $query->whereHas('locations', function ($q) use ($locationIds) {
                $q->whereIn('locations.id', $locationIds);
            });
        }

        if ($request->filled('role')) {
            $roleIds = explode(',', $request->input('role'));
            $query->whereHas('roles', function ($q) use ($roleIds) {
                $q->whereIn('roles.id', $roleIds);
            });
        }

        // ✅ Posted After filter
        if ($request->filled('posted_after')) {
            $query->whereDate('posted_at', '>=', $request->input('posted_after'));
        }

        // ✅ Manual pagination
        $total = $query->count();
        $jobs = $query->latest('posted_at')
                    ->skip(($page - 1) * $limit)
                    ->take($limit)
                    ->get();

        return response()->json([
            'data' => $jobs->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->job_title,
                    'slug' => $job->slug,
                    'description' => $job->description,
                    'short_description' => $job->short_description,
                    'posted_at' => Carbon::parse($job->posted_at)->toDateString(),
                    'expiry_date' => $job->expiry_date ? Carbon::parse($job->expiry_date)->toDateString() : null,
                    'image_url' => $job->image_path ? asset('storage/' . $job->image_path) : null,
                    'locations' => $job->locations->pluck('name'),
                    'roles' => $job->roles->pluck('name'),
                ];
            }),
            'meta' => [
                'page' => (int) $page,
                'limit' => (int) $limit,
                'total' => $total,
                'last_page' => ceil($total / $limit),
            ],
        ]);
    }

}
