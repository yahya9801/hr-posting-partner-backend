<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\BlogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/clean', function() {
 
    $routeClear = Artisan::call('route:clear');
    dump(Artisan::output());

    $configClear = Artisan::call('config:clear');
    dump(Artisan::output());

    $optimizeClear = Artisan::call('optimize:clear');
    dump(Artisan::output());    
    
    $viewClear = Artisan::call('view:clear');
    dump(Artisan::output());
    
    $viewCache = Artisan::call('view:cache');
    dump(Artisan::output());
    
    $cacheConfig = Artisan::call('config:cache');
    dump('config:cache'. Artisan::output());

    $migrate = Artisan::call('migrate');
    dump(Artisan::output());

    dump('----Done----');
    exit;
});

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

    Route::resource('jobs', JobController::class)->only(['index', 'create', 'show', 'edit', 'destroy', 'store', 'update']);
    Route::resource('blogs', BlogController::class);
});

