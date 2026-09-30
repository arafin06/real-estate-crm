<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Stripe webhook — public by design; the signature header is the authentication.
Route::post('/webhooks/stripe', [SubscriptionController::class, 'webhook']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    // Profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::patch('profile', [ProfileController::class, 'update']);
    Route::post('profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar']);

    // Billing
    Route::post('subscription/checkout', [SubscriptionController::class, 'createCheckoutSession']);
    Route::post('subscription/portal', [SubscriptionController::class, 'customerPortal']);

    // Agent-only example (for later modules)
    Route::middleware('role:agent,super_admin')->group(function () {
        // store is split out so the free-tier listing cap applies to it alone.
        Route::apiResource('properties', PropertyController::class)->except('store');
        Route::post('properties', [PropertyController::class, 'store'])
            ->middleware('subscription')
            ->name('properties.store');
        Route::post('properties/{property}/images', [PropertyController::class, 'addImages']);
        Route::delete('properties/{property}/images/{image}', [PropertyController::class, 'deleteImage']);
        Route::patch('properties/{property}/images/{image}/primary', [PropertyController::class, 'setPrimaryImage']);

        Route::apiResource('clients', ClientController::class);
        Route::post('clients/{client}/notes', [ClientController::class, 'addNote']);
        Route::delete('clients/{client}/notes/{note}', [ClientController::class, 'deleteNote']);

        Route::post('clients/{client}/phones', [ClientController::class, 'addPhone']);
        Route::patch('clients/{client}/phones/{phone}', [ClientController::class, 'updatePhone']);
        Route::delete('clients/{client}/phones/{phone}', [ClientController::class, 'deletePhone']);

        // Static path must be registered before the resource, or "kanban" is read as a deal ID.
        Route::get('deals/kanban', [DealController::class, 'kanban']);
        Route::apiResource('deals', DealController::class);
        Route::patch('deals/{deal}/stage', [DealController::class, 'updateStage']);
        Route::post('deals/{deal}/notes', [DealController::class, 'addNote']);
        Route::delete('deals/{deal}/notes/{note}', [DealController::class, 'deleteNote']);

        // Static path must be registered before the resource, or "summary" is read as a task ID.
        Route::get('tasks/summary', [TaskController::class, 'summary']);
        Route::get('tasks/grouped', [TaskController::class, 'grouped']);
        Route::post('tasks/{task}/complete-occurrence', [TaskController::class, 'completeOccurrence']);
        Route::apiResource('tasks', TaskController::class);
        Route::post('tasks/{task}/subtasks', [TaskController::class, 'storeSubtask']);
        Route::post('tasks/{task}/notes', [TaskController::class, 'addNote']);
        Route::delete('tasks/{task}/notes/{note}', [TaskController::class, 'deleteNote']);
    });
});
