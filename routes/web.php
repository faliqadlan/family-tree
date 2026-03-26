<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', fn(): JsonResponse => response()->json([
    'name' => config('app.name'),
    'message' => 'REST API is running.',
]));

Route::fallback(fn(): JsonResponse => response()->json([
    'message' => 'Not Found',
], 404));
