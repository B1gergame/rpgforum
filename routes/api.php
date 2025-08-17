<?php

use App\Http\Controllers\Api\SceneActorsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/scenes/{scene}/actors/available', [SceneActorsController::class, 'available']);

    Route::post('/scenes/{scene}/actors/attach', [SceneActorsController::class, 'attach']);
    Route::patch('/scenes/{scene}/actors/{actor}', [SceneActorsController::class, 'update']);
    Route::delete('/scenes/{scene}/actors/{actor}', [SceneActorsController::class, 'detach']);
});
