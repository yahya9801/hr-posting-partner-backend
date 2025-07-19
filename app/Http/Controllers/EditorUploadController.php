<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|max:2048',
        ]);

        // Store file in storage/app/public/jobs/
        $path = $request->file('upload')->store('jobs', 'public');

        // Return URL to CKEditor
        return response()->json([
            'url' => asset('storage/' . $path)
        ]);
    }
}
