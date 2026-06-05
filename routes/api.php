<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Chat endpoint
    Route::post('/chat', [ChatController::class, 'send'])
        ->middleware(['throttle:60,1'])
        ->name('api.chat.send');

    // History
    Route::get('/chat/history', [ChatController::class, 'history'])
        ->name('api.chat.history');

    // Delete conversation
    Route::delete('/chat/{conversation}', [ChatController::class, 'destroy'])
        ->name('api.chat.destroy');

});
