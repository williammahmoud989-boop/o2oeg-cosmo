<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SalonController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AIController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\TwilioWebhookController;

// Public Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/salons/register', [SalonController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password Reset Routes
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset');

// Social Auth Routes
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);

Route::get('/salons', [SalonController::class, 'index']);
Route::get('/salons/{salon}', [SalonController::class, 'show']);
Route::get('/search', [SalonController::class, 'search']);
Route::get('/bookings/availability', [BookingController::class, 'checkAvailability']);
Route::post('/ai/chat', [AIController::class, 'chat']);
Route::post('/ai/analyze-consultation', [AIController::class, 'analyzeConsultation']);

// Reviews (Public)
Route::get('/salons/{salon}/reviews', [ReviewController::class, 'index']);

// Services & Offers (Public)
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/offers', [OfferController::class, 'index']);

// Protected Endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('bookings', BookingController::class)->only(['index', 'store', 'show']);
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::post('coupons/validate', [CouponController::class, 'validateCoupon']);
    Route::post('payments/process', [PaymentController::class, 'process']);
    
    // Loyalty Routes
    Route::get('loyalty', [LoyaltyController::class, 'index']);
    Route::get('loyalty/transactions', [LoyaltyController::class, 'transactions']);
    
    // Reviews (Protected)
    Route::post('bookings/{booking}/review', [ReviewController::class, 'store']);
});

// Paymob Webhooks (Public)
Route::get('/payments/paymob/callback', [PaymentController::class, 'paymobCallback']);
Route::post('/payments/paymob/webhook', [PaymentController::class, 'paymobWebhook']);

// WhatsApp Webhook (Public)
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);

// Twilio Webhook (Public)
Route::post('/twilio/webhook', [TwilioWebhookController::class, 'handle'])->name('twilio.webhook');
