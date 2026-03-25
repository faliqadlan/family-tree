<?php

use App\Http\Controllers\Api\AccessRequestController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FamilyTreeController;
use App\Http\Controllers\Api\FinancialContributionController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Profiles & Privacy
    Route::get('/profiles/{profile}', [ProfileController::class, 'show']);
    Route::patch('/profiles/{profile}', [ProfileController::class, 'update']);

    // Access Requests (Privacy Handshake)
    Route::get('/access-requests', [AccessRequestController::class, 'index']);
    Route::post('/access-requests', [AccessRequestController::class, 'store']);
    Route::patch('/access-requests/{accessRequest}/respond', [AccessRequestController::class, 'respond']);

    // Events
    Route::apiResource('events', EventController::class);
    Route::post('/events/{event}/dispatch-invitations', [EventController::class, 'dispatchInvitations']);

    // Event Financial Contributions
    Route::get('/events/{event}/contributions', [FinancialContributionController::class, 'index']);
    Route::post('/events/{event}/contributions', [FinancialContributionController::class, 'store']);
    Route::patch('/events/{event}/contributions/{contribution}/confirm', [FinancialContributionController::class, 'confirm']);

    // Family Tree (Graph Queries)
    Route::get('/family-tree/descendants', [FamilyTreeController::class, 'descendants']);
});
