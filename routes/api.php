<?php

use App\Http\Controllers\Api\AccessRequestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FamilyTreeController;
use App\Http\Controllers\Api\FinancialContributionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RsvpController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::apiResource('users', UserController::class);

    Route::apiResource('profiles', ProfileController::class);

    Route::apiResource('access-requests', AccessRequestController::class)
        ->parameters(['access-requests' => 'accessRequest']);
    Route::patch('/access-requests/{accessRequest}/respond', [AccessRequestController::class, 'respond']);

    Route::apiResource('events', EventController::class);
    Route::post('/events/{event}/dispatch-invitations', [EventController::class, 'dispatchInvitations']);

    Route::apiResource('rsvps', RsvpController::class);

    Route::apiResource('financial-contributions', FinancialContributionController::class)
        ->parameters(['financial-contributions' => 'financialContribution']);
    Route::patch('/financial-contributions/{financialContribution}/confirm', [FinancialContributionController::class, 'confirm']);

    // Family Tree (Graph Queries)
    Route::get('/family-tree', [FamilyTreeController::class, 'index']);
    Route::get('/family-tree/descendants', [FamilyTreeController::class, 'descendants']);
});
