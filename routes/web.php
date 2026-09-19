<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WaliController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/pricing', [SubscriptionController::class, 'pricing'])->name('subscription.pricing');
Route::get('/photos/{photo}/view', [PhotoController::class, 'show'])->name('photos.view');

// Guest Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    if (app()->environment('local')) {
        Route::get('/demo-login/{role}', [AuthController::class, 'quickLogin'])->name('demo.login');
    }
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/account/deactivate', [AuthController::class, 'deactivate'])->name('account.deactivate');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photos', [ProfileController::class, 'uploadPhoto'])->name('profile.photos.upload');
    Route::post('/profile/photos/{id}/primary', [ProfileController::class, 'setPrimaryPhoto'])->name('profile.photos.primary');
    Route::delete('/profile/photos/{id}', [ProfileController::class, 'deletePhoto'])->name('profile.photos.delete');
    Route::post('/profile/photos/grant/{userId}', [ProfileController::class, 'grantPhotoAccess'])->name('profile.photos.grant');
    Route::post('/profile/verification', [ProfileController::class, 'submitVerification'])->name('profile.verification.submit');

    // Discovery & Matching
    Route::get('/discover', [DiscoveryController::class, 'index'])->name('discovery.index');
    Route::get('/profile/{id}', [DiscoveryController::class, 'show'])->name('discovery.show');
    Route::post('/saved-searches', [DiscoveryController::class, 'saveSearch'])->name('discovery.save-search');

    // Interests & Requests
    Route::get('/interests', [InterestController::class, 'index'])->name('interests.index');
    Route::post('/interests/{userId}', [InterestController::class, 'send'])->name('interests.send');
    Route::post('/interests/{id}/respond', [InterestController::class, 'respond'])->name('interests.respond');

    // Wali (Guardian) workflow
    Route::get('/wali/link', [WaliController::class, 'linkForm'])->name('wali.link');
    Route::post('/wali/link', [WaliController::class, 'linkStore'])->name('wali.link.store');
    Route::get('/wali/dashboard', [WaliController::class, 'dashboard'])->name('wali.dashboard');
    Route::post('/wali/interests/{id}/approve', [WaliController::class, 'approveInterest'])->name('wali.interests.approve');
    Route::post('/wali/interests/{id}/reject', [WaliController::class, 'rejectInterest'])->name('wali.interests.reject');

    // Halal Chaperoned Messaging
    Route::get('/messages', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/messages/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/messages/{id}', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/reports', [ChatController::class, 'reportUser'])->name('reports.store');

    // Subscription & Checkout
    Route::get('/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::post('/checkout/process', [SubscriptionController::class, 'process'])->name('subscription.process');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    // Admin & Moderation Console
    Route::middleware('moderator')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/verifications', [AdminController::class, 'verifications'])->name('verifications');
        Route::post('/verifications/{id}/approve', [AdminController::class, 'approveVerification'])->name('verifications.approve');
        Route::post('/verifications/{id}/reject', [AdminController::class, 'rejectVerification'])->name('verifications.reject');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::post('/reports/{id}/action', [AdminController::class, 'actionReport'])->name('reports.action');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/discounts', [AdminController::class, 'createDiscount'])->name('discounts.store');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
    });
});
