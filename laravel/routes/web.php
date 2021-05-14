<?php

use App\Http\Controllers\StatsController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});*/

/*Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');*/

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('stats.index');
    })->name('index');
    Route::resource('users', UserController::class);


    Route::get('/index', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/stats', [StatsController::class, 'stats'])->name('stats');

    Route::get('/check', [TopController::class, 'check'])->name('top.check');

    Route::get('/top', [TopController::class, 'index'])->name('top.index');

    Route::get('/browse', [TopController::class, 'browse'])->name('top.browse');



    Route::get('/show/{id}', [TopController::class, 'show'])->name('top.show');

});
