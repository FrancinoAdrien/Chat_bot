<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\AiProviderController;
use App\Http\Controllers\ApiConnectionController;
use App\Http\Controllers\ToolRelationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Redirect root to chat
    Route::get('/', fn() => redirect()->route('chat.index'));

    // Chat Interface
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::post('/send', [ChatController::class, 'send'])->name('send');
        Route::delete('/sessions/{session}', [ChatController::class, 'destroySession'])->name('sessions.destroy');
    });

    // Admin Only Routes
    Route::middleware('admin')->group(function () {
        // Tool Management
        Route::resource('tools', ToolController::class);
        Route::get('tools/{tool}/test', [ToolController::class, 'test'])->name('tools.test');

        // Tool Relations (ERD)
        Route::prefix('tool-relations')->name('tool-relations.')->group(function () {
            Route::get('/',                      [ToolRelationController::class, 'index'])->name('index');
            Route::get('/schema/{tool}',         [ToolRelationController::class, 'schema'])->name('schema');
            Route::post('/',                     [ToolRelationController::class, 'store'])->name('store');
            Route::delete('/{relation}',         [ToolRelationController::class, 'destroy'])->name('destroy');
        });

        // AI Rules (Directives)
        Route::resource('ai-rules', App\Http\Controllers\AiRuleController::class)->except(['create', 'show', 'edit']);
        Route::post('ai-rules/{ai_rule}/toggle', [App\Http\Controllers\AiRuleController::class, 'toggle'])->name('ai-rules.toggle');

        // AI Provider (Moteur IA)
        Route::prefix('ai-provider')->name('ai-provider.')->group(function () {
            Route::get('/',                                      [AiProviderController::class, 'index'])->name('index');
            Route::post('/',                                     [AiProviderController::class, 'store'])->name('store');
            Route::post('/verify',                               [AiProviderController::class, 'verify'])->name('verify');
            Route::post('/{aiProviderSetting}/activate',         [AiProviderController::class, 'activate'])->name('activate');
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

        // User Management
        Route::resource('users', UserController::class);
    });
});
