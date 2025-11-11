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
        logger($request->all());
        $query = Job::with('locations');
            // ->where(function ($q) {
            //     $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
            // })

        if ($request->filled('q')) {
            $searchTerm = $request->input('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('job_title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('short_description', 'like', "%{$searchTerm}%");
            });
        }

        // ✅ Location filtering
        if ($request->filled('locations')) {
            $locationIds = explode(',', $request->input('locations'));
            $query->whereHas('locations', function ($q) use ($locationIds) {
                $q->whereIn('locations.name', $locationIds);
            });
        }

        if ($request->filled('role')) {
            $roleIds = explode(',', $request->input('role'));
            $query->whereHas('roles', function ($q) use ($roleIds) {
                $q->whereIn('roles.name', $roleIds);
            });
        }

        // ✅ Posted After filter
        if ($request->filled('start')) {
            $query->whereDate('posted_at', '>=', $request->input('start'));
        }

        if ($request->filled('end')) {
            $query->whereDate('posted_at', '<=', $request->input('end'));
        }

        if ($request->filled('experience')) {
            $experienceNames = explode(',', $request->input('experience'));
            $query->whereHas('experiences', function ($q) use ($experienceNames) {
                $q->whereIn('name', $experienceNames);
            });
        }
        

        // ✅ Manual pagination
        $total = $query->count();
        $jobs = $query->orderBy('posted_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->skip(($page - 1) * $limit)
                    ->take($limit)
                    ->get();

        logger($jobs->toArray());

        return response()->json([
            'data' => $jobs->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->job_title,
                    'slug' => $job->slug,
                    'experiences' => $job->experiences->pluck('name'),
                    'description' => $job->description,
                    'short_description' => $job->short_description,
                    'posted_at' => Carbon::parse($job->posted_at)->toDateString(),
                    'expiry_date' => $job->expiry_date ? Carbon::parse($job->expiry_date)->toDateString() : null,
                    'images' => $job->images->map(fn($img) => asset('storage/' . $img->image_path)),
                    'locations' => $job->locations->pluck('name'),
                    'roles' => $job->roles->pluck('name'),
                    'views' => $job->views_count
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

    public function showBySlug($slug)
    {
        $job = Job::with(['locations', 'roles', 'experiences', 'images'])// eager load relationships if needed
                ->where('slug', $slug)
                ->first();
        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }
        $job->description = clean($job->description);

        return response()->json($job);
    }

    public function latest()
    {
        $jobs = Job::with('locations')
            ->orderByDesc('posted_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $data = $jobs->map(function (Job $job) {
            return [
                'id' => $job->id,
                'title' => $job->job_title,
                'slug' => $job->slug,
                'short_description' => $job->short_description,
                'posted_at' => $job->posted_at ? Carbon::parse($job->posted_at)->toDateString() : null,
                'locations' => $job->locations->pluck('name'),
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }


}
