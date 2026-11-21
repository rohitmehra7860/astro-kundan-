<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NewsletterController;

// Public routes
Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/tracking', [SettingsController::class, 'tracking']);
Route::get('/pages', [App\Http\Controllers\Api\PageController::class, 'index']);
Route::get('/pages/{slug}', [App\Http\Controllers\Api\PageController::class, 'show']);
Route::get('/sitemap', [App\Http\Controllers\Api\SitemapController::class, 'generate']);

// Public: Services APIs
Route::get('/services', [App\Http\Controllers\Api\ServiceApiController::class, 'index']);
Route::get('/services/categories', [App\Http\Controllers\Api\ServiceApiController::class, 'categories']);
Route::get('/services/{slug}', [App\Http\Controllers\Api\ServiceApiController::class, 'show']);

// Public: Comments (read + submit, no auth needed)
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->middleware('throttle:10,1');

// Public: Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,1');
Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe']);

// Public: Contact Leads
Route::post('/contact-leads', [\App\Modules\ContactLeads\Controllers\ContactLeadController::class, 'store'])->middleware('throttle:5,1');

// Public Auth routes
Route::post('/register',     [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login',        [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->middleware('throttle:10,1');
Route::post('/resend-otp',   [AuthController::class, 'resendOtp'])->middleware('throttle:3,1');

// Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Posts (Blog) APIs
    Route::apiResource('posts', BlogController::class);
    Route::get('/posts/{post}/related', [BlogController::class, 'related']);

    // Contact Leads APIs (Protected)
    Route::get('/contact-leads', [\App\Modules\ContactLeads\Controllers\ContactLeadController::class, 'index']);
    Route::get('/contact-leads/{contactLead}', [\App\Modules\ContactLeads\Controllers\ContactLeadController::class, 'show']);
});
