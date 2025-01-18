<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\PromptLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/prompt-log', [PromptLogController::class, 'store']);
Route::get('/prompt-log/most-used', [PromptLogController::class, 'mostUsedPrompts']);
Route::get('/prompt-log', [PromptLogController::class, 'getLogs']);


Route::prefix('admin')->group(function () {
    Route::get('/metrics', [AdminDashboardController::class, 'getMetrics']);
    Route::get('/suggestions', [AdminDashboardController::class, 'getSuggestions']);
});