<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MembersController;
use App\Http\Controllers\OtpAuthController;
use App\Http\Controllers\AuthController; // if you kept previous login/logout
use App\Http\Controllers\Admin\MembersController as AdminMembersController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\AllianceController;
use App\Http\Controllers\AlliancePaymentController;
use App\Http\Controllers\Admin\AllianceController as AdminAllianceController;
use App\Http\Controllers\PastorateComMemberController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\PastorController as AdminPastorController;
use App\Http\Controllers\PoorFeedingController;
use App\Http\Controllers\MenFellowshipController;
use App\Http\Controllers\WomenFellowshipController;
use Illuminate\Console\View\Components\Warn;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\AnniversaryController;
use App\Http\Controllers\Admin\AnniversaryController as AdminAnniversaryController;




Route::middleware('api')->group(function () {

    Route::post('/members', [MembersController::class, 'store']);
});

Route::post('otp/send', [OtpAuthController::class, 'send'])
    ->name('otp.send');

Route::post('otp/verify', [OtpAuthController::class, 'verify'])
    ->name('otp.verify');

// Protected endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', function (Illuminate\Http\Request $request) {
        return response()->json(['success' => true, 'member' => $request->user()]);
    })->name('api.me');

    Route::post('logout', function (Illuminate\Http\Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out']);
    })->name('api.logout');
});


Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('members', [AdminMembersController::class, 'index'])->name('members.index');
    Route::get('members/{member}', [AdminMembersController::class, 'show'])->name('members.show');
    Route::put('members/{member}', [AdminMembersController::class, 'update'])->name('members.update');
    Route::patch('members/{member}', [AdminMembersController::class, 'update']);
    Route::delete('members/{member}', [AdminMembersController::class, 'destroy'])->name('members.destroy');
    Route::post('members',  [AdminMembersController::class, 'store'])->name('members.store');
    Route::patch('members/{member}/contact', [AdminMembersController::class, 'updateContact']);
    Route::patch('members/{member}/deactivate', [AdminMembersController::class, 'deactivate']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Member: view own subscription due & create order for payment
    Route::get('subscriptions/my/due', [SubscriptionController::class, 'myDue']);
    Route::post('subscriptions/my/pay', [SubscriptionController::class, 'pay']); // creates order, returns order id for client checkout
});

// Admin routes (admin middleware)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('subscriptions/{member}/pay', [AdminSubscriptionController::class, 'payOnBehalf']);
    Route::post('subscriptions/verify-payment', [AdminSubscriptionController::class, 'verifyPayment']);
    Route::post('subscriptions/{member}/create', [AdminSubscriptionController::class, 'createSubscription']);
    Route::get('subscriptions/{member}/due', [AdminSubscriptionController::class, 'due']);
    Route::get('subscriptions', [AdminSubscriptionController::class, 'index']);
    Route::get('subscriptions/{member}', [AdminSubscriptionController::class, 'show']);
    Route::get('payments/{payment}/receipt', [AdminSubscriptionController::class, 'show']);
    Route::post('subscriptions/{member}/pay-offline', [AdminSubscriptionController::class, 'payOffline']);
});

Route::post('razorpay/webhook', [RazorpayWebhookController::class, 'handle']); // webhook: verify secret

// Member routes (must be authenticated)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('alliances', [AllianceController::class, 'store'])->name('alliances.store');
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Option A: reuse same controller method but hit a separate endpoint
    //  Route::post('alliances', [AllianceController::class, 'storeByAdmin'])
    //     ->name('admin.alliances.store');

    // Option B (alternative): use separate Admin controller (uncomment if you want)
    Route::post('alliances', [AdminAllianceController::class, 'store'])->name('admin.alliances.store');
    Route::get('alliances', [AdminAllianceController::class, 'index'])->name('admin.alliancees.index');
    Route::get('alliances/{alliance}', [AdminAllianceController::class, 'show'])->name('admin.alliances.show');
    Route::patch('alliances/{alliance}', [AdminAllianceController::class, 'update'])->name('admin.alliances.update');
    Route::patch('alliances/{alliance}/publish', [AdminAllianceController::class, 'togglePublish'])->name('admin.alliances.publish');
});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('alliances/{alliance}/payments/create-order', [AlliancePaymentController::class, 'createOrder'])
        ->name('alliances.payments.createOrder');

    Route::post('alliances/{alliance}/payments/verify', [AlliancePaymentController::class, 'verify'])
        ->name('alliances.payments.verify');
});


Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('pastorate-members', PastorateComMemberController::class);
});

// Admin protected CRUD
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('events', [EventController::class, 'store'])->name('admin.events.store');
    Route::put('events/{event}', [EventController::class, 'update'])->name('admin.events.update');
    Route::patch('events/{event}', [EventController::class, 'update']);
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('admin.events.destroy');

    // optional endpoint to remove a single photo
    Route::delete('events/{event}/photo', [EventController::class, 'removePhoto'])->name('admin.events.photo.remove');
});


// Admin-only routes (CRUD)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('announcements', AdminAnnouncementController::class); // since those are public
});


Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('pastors', AdminPastorController::class)->except(['destroy']);
});


Route::get('poor-feedings', [PoorFeedingController::class, 'index']);
Route::get('poor-feedings/{poorFeeding}', [PoorFeedingController::class, 'show']);

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('poor-feedings', [PoorFeedingController::class, 'store']);
    Route::put('poor-feedings/{poorFeeding}', [PoorFeedingController::class, 'update']);
    Route::patch('poor-feedings/{poorFeeding}', [PoorFeedingController::class, 'update']);
    Route::delete('poor-feedings/{poorFeeding}', [PoorFeedingController::class, 'destroy']);

    // optional remove one photo endpoint
    Route::delete('poor-feedings/{poorFeedings}/photo', [PoorFeedingController::class, 'removePhoto']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('men-fellowships', [MenFellowshipController::class, 'store']);
    Route::put('men-fellowships/{menFellowship}', [MenFellowshipController::class, 'update']);
    Route::patch('men-fellowships/{menFellowship}', [MenFellowshipController::class, 'update']);
    Route::delete('men-fellowships/{menFellowship}', [MenFellowshipController::class, 'destroy']);
    Route::delete('men-fellowships/{menFellowship}/photo', [MenFellowshipController::class, 'removePhoto']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('women-fellowships', [WomenFellowshipController::class, 'store']);
    Route::put('women-fellowships/{womenFellowship}', [WomenFellowshipController::class, 'update']);
    Route::patch('women-fellowships/{womenFellowship}', [WomenFellowshipController::class, 'update']);
    Route::delete('women-fellowships/{womenFellowship}', [WomenFellowshipController::class, 'destroy']);

    // optional remove photo endpoint
    Route::delete('women-fellowships/{womenFellowship}/photo', [WomenFellowshipController::class, 'removePhoto']);
});


Route::get('members/birthdays/today', [BirthdayController::class, 'today']);

Route::get('members/birthdays/upcoming', [BirthdayController::class, 'upcomingWeek']);

Route::get('/check-user', function (Illuminate\Http\Request $request) {
    return response()->json([
        'authenticated_user' => $request->user(),
        'token' => $request->bearerToken(),
    ]);
})->middleware('auth:sanctum');
