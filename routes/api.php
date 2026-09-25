<?php

use App\Http\Controllers\API\AdminApprovalController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDetailController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\SellerWalletController;
use App\Http\Controllers\Api\WalletTransactionController;
use App\Http\Controllers\Api\WithdrawalRequestController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\LessonProgressController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\NotificationController;

use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/update-profile', [AuthController::class, 'updateProfile']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::post('/upload-avatar', [AuthController::class, 'uploadAvatar']);

Route::resource('user', UserController::class);
// Route cập nhật dùng POST để hỗ trợ multipart/form-data
Route::post('/users/{id}', [UserController::class, 'update']);

// Route đổi trạng thái nhanh (Khóa / Mở khóa)
Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);



// Category
Route::resource('category', CategoryController::class);
Route::get('/category/{slug}', [CategoryController::class, 'show']);


// Course

Route::get('/courses', [CourseController::class, 'index']);
Route::put('/courses/{id}', [CourseController::class, 'update']);
Route::post('/courses/{id}/image', [CourseController::class, 'updateImage']);
// Route::get('/admin/approval/sellers', [
//     AuthController::class,
//     'getPendingSellers'
// ]);

// Route::get('/admin/approval/sellers/{id}', [
//     AuthController::class,
//     'getPendingSellerById'
// ]);

// Route::put('/admin/approval/sellers/{id}/approve', [
//     AuthController::class,
//     'approveSeller'
// ]);

// Route::put('/admin/approval/sellers/{id}/reject', [
//     AuthController::class,
//     'rejectSeller'
// ]);

Route::prefix('admin/approval')->group(function () {

    Route::get('/sellers', [
        AdminApprovalController::class,
        'getPendingSellers'
    ]);

    Route::get('/sellers/{id}', [
        AdminApprovalController::class,
        'getPendingSellerById'
    ]);

    Route::put('/sellers/{id}/approve', [
        AdminApprovalController::class,
        'approveSeller'
    ]);

    Route::put('/sellers/{id}/reject', [
        AdminApprovalController::class,
        'rejectSeller'
    ]);

    Route::get('/courses', [
        AdminApprovalController::class,
        'getPendingCourses'
    ]);

    Route::get('/courses/{id}', [
        AdminApprovalController::class,
        'getPendingCourseById'
    ]);

    Route::put('/courses/{id}/approve', [
        AdminApprovalController::class,
        'approveCourse'
    ]);

    Route::put('/courses/{id}/reject', [
        AdminApprovalController::class,
        'rejectCourse'
    ]);
});
// Lesson
Route::resource('lesson', LessonController::class);

// Order
Route::resource('order', OrderController::class);

// Order Detail
Route::resource('order-detail', OrderDetailController::class);

// Payment
Route::resource('payment', PaymentController::class);

// Payment Webhook
Route::resource('payment-webhook', PaymentWebhookController::class);

// Seller Wallet
Route::resource('seller-wallet', SellerWalletController::class);

// Wallet Transaction
Route::resource('wallet-transaction', WalletTransactionController::class);

// Withdrawal Request
Route::resource('withdrawal-request', WithdrawalRequestController::class);

// Enrollment
Route::resource('enrollment', EnrollmentController::class);

// Lesson Progress
Route::resource('lesson-progress', LessonProgressController::class);

// Review
Route::resource('review', ReviewController::class);

// Notification
Route::resource('notification', NotificationController::class);
Route::middleware(['auth:sanctum', 'seller'])
    ->prefix('seller')
    ->group(function () {

        Route::post('/courses', [CourseController::class, 'store']);

    });