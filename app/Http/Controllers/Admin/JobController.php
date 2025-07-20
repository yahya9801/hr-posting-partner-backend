<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Location;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier; // if using mews/purifier

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::latest()->paginate(10); // Show 10 jobs per page

        return view('admin.jobs.index', compact('jobs'));
    }

    public function create()
    {
        // $locations = \App\Models\Location::orderBy('name')->get();
        return view('admin.jobs.create');
    }

    public function show(Job $job)
    {
        $job->load('locations'); // eager load relationships\
        $job->load('roles');
        return view('admin.jobs.show', compact('job'));
    }
      
    public function store(Request $request)
    {
        $data = $request->validate([
            'job_title'   => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'posted_at'   => 'required|date',
            'image'       => 'nullable|image|max:2048',
            'locations'   => 'required|array',
            'roles'       => 'required|array',
        ]);
        // dd($data);
        // ✅ Sanitize rich HTML
        // $data['description'] = Purifier::clean($data['description'] ?? '');
    
        // ✅ Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('jobs', 'public');
        }
        $slug = $request->input('slug');
        $data['slug'] = $slug
            ? Job::generateUniqueSlug($slug)
            : Job::generateUniqueSlug($data['job_title']);

        // ✅ Create the job
        $job = Job::create([
            'job_title'   => $data['job_title'],
            'slug'       => $data['slug'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'posted_at'   => $data['posted_at'],
            'image_path'  => $data['image_path'] ?? null,
        ]);
    
        // ✅ Sync locations
        $locationIds = [];
        foreach ($data['locations'] as $loc) {
            if (is_numeric($loc)) {
                $locationIds[] = $loc;
            } else {
                $location = Location::firstOrCreate(['name' => $loc]);
                $locationIds[] = $location->id;
            }
        }
        $job->locations()->sync($locationIds);
    
        // ✅ Sync roles
        $roleIds = [];
        foreach ($data['roles'] as $role) {
            if (is_numeric($role)) {
                $roleIds[] = $role;
            } else {
                $roleModel = Role::firstOrCreate(['name' => $role]);
                $roleIds[] = $roleModel->id;
            }
        }
        $job->roles()->sync($roleIds);

        // dd($job);
    
        return redirect()->route('admin.jobs.index')->with('success', 'Job created successfully.');
    }

    public function edit(Job $job)
    {
        $locations = Location::all();
        $roles = Role::all();
        $job->load('locations'); // eager load relationships\
        $job->load('roles');
        return view('admin.jobs.edit', compact('job', 'locations', 'roles'));
    }

    public function update(Request $request, Job $job)
    {
        $data = $request->validate([
            'job_title' => 'required|string|max:255',
            // 'slug' => 'nullable|string|max:255|unique:jobs,slug,' . $job->id,
            'short_description' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            'posted_at' => 'required|date',
            // 'expiry_date' => 'nullable|date|after_or_equal:today',
            'image' => 'nullable|image|max:2048',
            'locations' => 'required|array',
            'roles' => 'required|array',
        ]);
    
        // Update slug if provided, or regenerate if title changed
        if ($request->filled('slug')) {
            $job->slug = Job::generateUniqueSlug($request->input('slug'));
        } elseif ($job->isDirty('job_title')) {
            $job->slug = Job::generateUniqueSlug($data['job_title']);
        }
    
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('jobs', 'public');
        }
    
        $job->update([
            'job_title' => $data['job_title'],
            'slug' => $job->slug,
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'posted_at' => $data['posted_at'],
            // 'expiry_date' => $data['expiry_date'],
            'image_path' => $data['image_path'] ?? $job->image_path,
        ]);
    
        // Sync locations
        $locationIds = [];
        foreach ($data['locations'] as $loc) {
            if (is_numeric($loc)) {
                $locationIds[] = $loc;
            } else {
                $location = Location::firstOrCreate(['name' => $loc]);
                $locationIds[] = $location->id;
            }
        }
        $job->locations()->sync($locationIds);

        $roleIds = [];
        // dd($data['roles']);
        foreach ($data['roles'] as $role) {
            if (is_numeric($role)) {
                $roleIds[] = $role;
            } else {
                $roleModel = Role::firstOrCreate(['name' => $role]);
                $roleIds[] = $roleModel->id;
            }
        }
        // dd($roleIds);
        $job->roles()->sync($roleIds);
    
        return redirect()->route('admin.jobs.index')->with('success', 'Job updated successfully.');
    }
    
    public function destroy(Job $job)
    {
        if ($job->image_path) {
            Storage::disk('public')->delete($job->image_path);
        }

        $job->delete();

        return redirect()->route('admin.jobs.index')->with('success', 'Job deleted successfully.');
    }
}
