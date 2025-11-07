<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EditorUploadController;
use App\Http\Controllers\JobsApiController;
use App\Http\Controllers\LocationApiController;
use App\Http\Controllers\BlogCategoryApiController;
use App\Http\Controllers\BlogPostApiController;
use App\Http\Controllers\JobViewController;

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
Route::get('/experience', [RoleController::class, 'experience']);

Route::post('/editor-upload', [EditorUploadController::class, 'store']);

Route::get('/jobs', [JobsApiController::class, 'index']);
Route::get('/jobs/latest', [JobsApiController::class, 'latest'])
    ->middleware('throttle:60,1');
Route::get('/jobs/{slug}', [JobsApiController::class, 'showBySlug']);
Route::get('/jobs/{job}/views', [JobViewController::class, 'show']);
Route::post('/jobs/{job}/view', [JobViewController::class, 'store']);
Route::get('/blogs/categories/top', [BlogCategoryApiController::class, 'top'])
    ->middleware('throttle:60,1');
Route::get('/blogs/categories', [BlogCategoryApiController::class, 'index']);
Route::get('/blogs/categories/{slug}/posts', [BlogCategoryApiController::class, 'postsBySlug'])
    ->where('slug', '[A-Za-z0-9-]+');
Route::get('/blogs/featured', [BlogPostApiController::class, 'featured']);
Route::get('/blogs/latest', [BlogPostApiController::class, 'latest'])
    ->middleware('throttle:60,1');
Route::get('/blogs/categories/{slug}/latest', [BlogCategoryApiController::class, 'latestBySlug'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->middleware('throttle:60,1');
Route::get('/blogs/categories-excluding/{slug}', [BlogCategoryApiController::class, 'excludeSlug'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->middleware('throttle:60,1');
Route::get('/blogs/search', [BlogPostApiController::class, 'search']);
Route::get('/blogs/{slug}', [BlogPostApiController::class, 'show'])
    ->where('slug', '[A-Za-z0-9-]+');

// Route::get('/locations', [LocationApiController::class, 'index']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
