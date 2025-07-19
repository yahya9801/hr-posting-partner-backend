<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationApiController extends Controller
{
    // ✅ Fetch all or filtered locations
    public function index(Request $request)
    {
        $query = Location::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->input('q') . '%');
        }

        $locations = $query->orderBy('name')->limit(50)->get();

        return response()->json($locations->map(function ($loc) {
            return [
                'id' => $loc->id,
                'name' => $loc->name,
            ];
        }));
    }

    // ✅ Create new location (optional)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
        ]);

        $location = Location::create($data);

        return response()->json([
            'id' => $location->id,
            'name' => $location->name,
        ], 201);
    }
}

