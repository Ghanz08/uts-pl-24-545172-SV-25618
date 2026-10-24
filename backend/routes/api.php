<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectIdeaController;
use Illuminate\Support\Facades\Route;

// Login is the only public endpoint; everything else needs a Sanctum token.
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/project-ideas/roulette', [ProjectIdeaController::class, 'roulette']);
    Route::get('/project-ideas/stats', [ProjectIdeaController::class, 'stats']);
    Route::apiResource('project-ideas', ProjectIdeaController::class)
        ->parameters(['project-ideas' => 'id'])
        ->whereNumber('id');
});
