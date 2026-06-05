<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\AiProviderController;
use App\Http\Controllers\ApiConnectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to chat
Route::get('/', fn() => redirect()->route('chat.index'));

// Chat Interface
Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::post('/send', [ChatController::class, 'send'])->name('send');
});



// Tool Management
Route::resource('tools', ToolController::class);
Route::get('tools/{tool}/test', [ToolController::class, 'test'])->name('tools.test');

// AI Provider (Moteur IA)
Route::prefix('ai-provider')->name('ai-provider.')->group(function () {
    Route::get('/',                                      [AiProviderController::class, 'index'])->name('index');
    Route::post('/',                                     [AiProviderController::class, 'store'])->name('store');
    Route::post('/verify',                               [AiProviderController::class, 'verify'])->name('verify');
    Route::post('/{aiProviderSetting}/deactivate',       [AiProviderController::class, 'deactivate'])->name('deactivate');
    Route::delete('/{aiProviderSetting}',                [AiProviderController::class, 'destroy'])->name('destroy');
});

// API Connections Management
Route::prefix('connections')->name('connections.')->group(function () {
    Route::get('/',                                    [ApiConnectionController::class, 'index'])->name('index');
    Route::post('/',                                   [ApiConnectionController::class, 'store'])->name('store');
    Route::put('/{connection}',                        [ApiConnectionController::class, 'update'])->name('update');
    Route::delete('/{connection}',                     [ApiConnectionController::class, 'destroy'])->name('destroy');
    Route::post('/{connection}/authenticate',          [ApiConnectionController::class, 'authenticate'])->name('authenticate');
    Route::post('/{connection}/store-token',           [ApiConnectionController::class, 'storeToken'])->name('store-token');
    Route::post('/{connection}/disconnect',            [ApiConnectionController::class, 'disconnect'])->name('disconnect');
    Route::get('/{connection}/ping',                   [ApiConnectionController::class, 'ping'])->name('ping');
});
