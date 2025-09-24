<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PracticeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth')->group(function () {
    Route::get('/pratiche', [PracticeController::class, 'index']);
    Route::post('/pratiche', [PracticeController::class, 'store']);
    Route::get('/pratiche/{id}', [PracticeController::class, 'show']);
    Route::put('/pratiche/{id}', [PracticeController::class, 'update']);
    Route::delete('/pratiche/{id}', [PracticeController::class, 'destroy']);
    Route::post('/pratiche/{id}/force-delete', [PracticeController::class, 'forceDelete']);
    Route::get('/pratiche-alerts', [PracticeController::class, 'alerts']);
    Route::post('/pratiche/{id}/schedule-delete', [PracticeController::class, 'scheduleDelete']);
});
