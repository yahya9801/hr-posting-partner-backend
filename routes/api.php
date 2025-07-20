<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EditorUploadController;
use App\Http\Controllers\JobsApiController;
use App\Http\Controllers\LocationApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Login & logout
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
Route::get('/locations', [LocationController::class, 'index']);
Route::get('/roles', [RoleController::class, 'index']);
Route::post('/editor-upload', [EditorUploadController::class, 'store']);

Route::get('/jobs', [JobsApiController::class, 'index']);
Route::get('/jobs/{slug}', [JobsApiController::class, 'showBySlug']);

// Route::get('/locations', [LocationApiController::class, 'index']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
