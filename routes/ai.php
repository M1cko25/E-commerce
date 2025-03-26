<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiController;

Route::post('/ai/process', [AiController::class, 'processPrompt']);
Route::get('/ai/chat-history', [AiController::class, 'getChatHistory']);
Route::get('/ai/conversation/{id}', [AiController::class, 'getConversation']);
Route::post('/ai/add-to-cart', [AiController::class, 'addToCart']);
Route::delete('/ai/chat-history/{id}', [AiController::class, 'deletePromptHistory']);

