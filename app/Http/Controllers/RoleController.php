<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $roles = Role::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->limit(10)
            ->get(['id', 'name as text']); // Select2 expects `id` and `text`

        return response()->json($roles);
    }
}
