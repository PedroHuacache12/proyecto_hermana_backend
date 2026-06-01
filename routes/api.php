<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PublicCatalogController;
use Illuminate\Support\Facades\Route;

// Rutas públicas del catálogo (sin auth)
Route::get('/c/{slug}', [PublicCatalogController::class, 'show']);
Route::post('/c/{slug}/register', [PublicCatalogController::class, 'register']);
Route::post('/c/{slug}/track', [PublicCatalogController::class, 'track']);
Route::get('/c/{slug}/actions', [PublicCatalogController::class, 'actions']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);

    Route::apiResource('products', ProductController::class);
    Route::post('products/upload-image', [ProductController::class, 'uploadImage']);

    Route::apiResource('catalogs', CatalogController::class);
    Route::post('catalogs/{catalog}/publish', [CatalogController::class, 'publish']);
    Route::post('catalogs/{catalog}/products', [CatalogController::class, 'syncProducts']);

    Route::get('analytics', [AnalyticsController::class, 'index']);
    Route::get('analytics/catalogs', [AnalyticsController::class, 'catalogs']);
    Route::get('analytics/catalogs/{catalog}', [AnalyticsController::class, 'catalog']);
});
