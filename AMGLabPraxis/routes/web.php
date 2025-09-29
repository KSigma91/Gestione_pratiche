<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Models\Practice;

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

// Rotta per mettere in giacenza una singola pratica
//Route::post('/admin/pratiche/{id}/giacenza', [AdminController::class, 'markGiacenza'])->name('admin.pratiche.giacenza');

// mostra la partial di esempio (opzionale: utile per debug)
Route::get('/admin/giacenza/dropdown', function () {
    return view('partials.stock_dropdown');
})->name('admin.giacenza.dropdown');


Route::middleware('auth')->group(function () {
    // Pratiche admin - Blade
    Route::get('/admin/pratiche', [AdminController::class, 'index'])->name('admin.pratiche.index');
    Route::get('/admin/pratiche/nuova-pratica', [AdminController::class, 'create'])->name('admin.pratiche.create');
    Route::post('/admin/pratiche', [AdminController::class, 'store'])->name('admin.pratiche.store');
    Route::get('/admin/pratiche/{id}/modifica', [AdminController::class, 'edit'])->name('admin.pratiche.edit');
    Route::put('/admin/pratiche/{id}', [AdminController::class, 'update'])->name('admin.pratiche.update');

    // cestino
    Route::get('/admin/pratiche/cestino', [AdminController::class, 'trash'])->name('admin.pratiche.trash');
    Route::post('/admin/pratiche/{id}/ripristina', [AdminController::class, 'restore'])->name('admin.pratiche.restore');
    Route::post('/admin/pratiche/{id}/force-delete', [AdminController::class, 'forceDelete'])->name('admin.pratiche.force-delete');

    // Other actions
    Route::post('/admin/pratiche/{id}/delete', [AdminController::class, 'destroy'])->name('admin.pratiche.delete');
    Route::post('/admin/pratiche/{id}/giacenza', [AdminController::class, 'markGiacenza'])->name('admin.pratiche.giacenza');

    // Archivio per anno e mese
    Route::get('/admin/pratiche/archivio', [AdminController::class, 'archiveIndex'])->name('admin.pratiche.archive');
    Route::get('/admin/pratiche/archivio/{year}/{month}', [AdminController::class, 'archiveView'])->name('admin.pratiche.archive.view');
});
