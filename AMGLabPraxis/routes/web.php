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

Auth::routes();

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('admin.pratiche.index');
    }
    return redirect()->route('login');
});

Route::get('/home', function () {
    return redirect()->route('admin.dashboard');
})->name('home');

Route::middleware('auth', 'no.cache')->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Sessioni admin
    Route::get('/sessions', 'SessionController@index')->name('sessions.index');
    Route::post('/sessions/kill/{sessionId}', 'SessionController@destroy')->name('sessions.destroy');
    Route::post('/sessions/kill-others', 'SessionController@destroyOthers')->name('sessions.destroy.others');

    // Pratiche admin
    Route::get('/pratiche', [AdminController::class, 'index'])->name('pratiche.index');
    Route::get('/pratiche/nuova-pratica', [AdminController::class, 'create'])->name('pratiche.create');
    Route::post('/pratiche', [AdminController::class, 'store'])->name('pratiche.store');
    Route::get('/pratiche/{id}/modifica', [AdminController::class, 'edit'])->name('pratiche.edit');
    Route::put('/pratiche/{id}', [AdminController::class, 'update'])->name('pratiche.update');
    Route::get('/pratiche/{id}/visualizza-pratica', [AdminController::class, 'show'])->name('pratiche.show');

    // Cestino
    Route::get('/pratiche/cestino', [AdminController::class, 'trash'])->name('pratiche.trash');
    Route::post('/pratiche/{id}/ripristina', [AdminController::class, 'restore'])->name('pratiche.restore');
    Route::post('/pratiche/{id}/force-delete', [AdminController::class, 'forceDelete'])->name('pratiche.force-delete');

    // Altre azioni
    Route::post('/pratiche/{id}/delete', [AdminController::class, 'destroy'])->name('pratiche.delete');
    Route::post('/pratiche/{id}/giacenza', [AdminController::class, 'markGiacenza'])->name('pratiche.giacenza');
    Route::post('/pratiche/{id}/rimuovi-giacenza', [AdminController::class, 'removeGiacenza'])->name('pratiche.remove_giacenza');
    Route::post('/notifiche/{id}/letta', 'AdminController@markNotificaLetta')->name('notifiche.markLetta');

    // Archivio per anno e mese
    Route::get('/pratiche/archivio', [AdminController::class, 'archiveIndex'])->name('pratiche.archive');
    Route::get('/pratiche/archivio/{year}/{month}', [AdminController::class, 'archiveView'])->name('pratiche.archive.view');

    // Esporta per anno (CSV, Excel, Word, PDF)
    Route::get('/pratiche/esporta/{year}/csv', 'AdminController@exportYearCsv')->name('pratiche.export.year.csv');
    Route::get('/pratiche/esporta/{year}/excel', 'AdminController@exportYearExcel')->name('pratiche.export.year.excel');
    Route::get('/pratiche/esporta/{year}/word', 'AdminController@exportYearWord')->name('pratiche.export.year.word');
    Route::get('/pratiche/esporta/{year}/pdf', 'AdminController@exportYearPdf')->name('pratiche.export.year.pdf');

    // Logs attività
    Route::get('/logs', [AdminController::class, 'activityLogs'])->name('logs');
    Route::get('/logs/partial', [AdminController::class, 'activityLogsPartial'])->name('logs.partial');
});
