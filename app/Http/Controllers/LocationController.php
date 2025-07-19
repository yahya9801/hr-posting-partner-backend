<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $locations = Location::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->limit(10)
            ->get(['id', 'name as text']); // required format for Select2

        return response()->json($locations);
    }
}

