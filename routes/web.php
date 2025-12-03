<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatHelperController;

Route::get('/', function () {
    return view('welcome');
});

// Public chat helper
Route::get('/chat-helper', [ChatHelperController::class, 'index'])->name('chat-helper.index');
Route::post('/chat-helper', [ChatHelperController::class, 'send'])->name('chat-helper.send');
