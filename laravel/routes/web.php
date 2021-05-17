<?php

use App\Http\Controllers\StatsController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\UserController;
use App\Models\RblLog;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

    Route::get('/logs', function() {
        return Inertia::render('Stats/Logs', [
            'sortColumns' => [
                ['name' => 'id', 'class' => 'w-1/12'],
                ['name' => __('User'), 'class' => 'w-1/12'],
                ['name' => __('Date'), 'class' => 'w-2/12'],
                ['name' => __('Type'), 'class' => 'w-2/12']
            ]
        ]);
    })->name('stats.logs');
    Route::get('/get-logs', [StatsController::class, 'getLogs'])->name('log.jslogs');
    Route::model('rbllog', RblLog::class);
    Route::post('/read-log/{rbllog}', [StatsController::class, 'readLog'])->name('log.read');
    Route::delete('/delete-log/{rbllog}', [StatsController::class, 'deleteLog'])->name('log.delete');


    Route::get('/index', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/stats', [StatsController::class, 'stats'])->name('stats');

    Route::get('/check', [TopController::class, 'check'])->name('top.check');

    Route::get('/top', [TopController::class, 'index'])->name('top.index');

    Route::get('/browse', [TopController::class, 'browse'])->name('top.browse');
    Route::post('/browse', [TopController::class, 'browse'])->name('top.getBrowse');



    Route::get('/show/{id}', [TopController::class, 'show'])->name('top.show');
    Route::post('/show/{id}', [TopController::class, 'show'])->name('update.show');

    Route::get('/ip-log', [TopController::class, 'ipLog'])->name('ip.log');
    Route::get('/history/{id}', [TopController::class, 'history'])->name('top.history');

    Route::get('/toggle/{id}/{field}/{msg?}', [TopController::class, 'toggle'])->name('top.toggle');
    Route::post('/toggle/{id}/{field}/{msg?}', [TopController::class, 'toggle'])->name('update.toggle');

});
