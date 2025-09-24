<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('admin.pratiche.index');
    }
    return redirect()->route('login');
});

Route::get('/home', function () {
    return redirect()->route('admin.pratiche.index');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/admin/pratiche', [AdminController::class, 'index'])->name('admin.pratiche.index');
    Route::post('/admin/pratiche/{id}/delete', [AdminController::class, 'destroy'])->name('admin.pratiche.delete');
    Route::post('/admin/pratiche/{id}/force-delete', [AdminController::class, 'forceDelete'])->name('admin.pratiche.force-delete');
    Route::post('/admin/pratiche/{id}/schedule-delete', [AdminController::class, 'scheduleDelete'])->name('admin.pratiche.schedule-delete');
});
