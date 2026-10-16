<?php

use App\Http\Controllers\Auth\ForcedPasswordChangeController;
use App\Http\Controllers\Coordinator\ArchiveController;
use App\Http\Controllers\Coordinator\AuditLogController;
use App\Http\Controllers\Coordinator\PasswordResetController;
use App\Http\Controllers\Coordinator\StaffProvisioningController;
use App\Http\Controllers\Coordinator\StudentApprovalController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
    ]);
})->name('health');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Forced Password Change on First Login or Reset
Route::middleware('auth')->group(function () {
    Route::get('/password/forced-change', [ForcedPasswordChangeController::class, 'show'])
        ->name('password.forced-change');
    Route::post('/password/forced-change', [ForcedPasswordChangeController::class, 'update'])
        ->name('password.forced-update');
});

// Profile Management
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Coordinator Module Routes (OC / COC)
Route::middleware(['auth', 'verified'])->prefix('coordinator')->name('coordinator.')->group(function () {
    // Student Account Approval Queue
    Route::get('/students/pending', [StudentApprovalController::class, 'index'])
        ->name('students.pending');
    Route::post('/students/{user}/approve', [StudentApprovalController::class, 'approve'])
        ->name('students.approve');
    Route::post('/students/{user}/reject', [StudentApprovalController::class, 'reject'])
        ->name('students.reject');

    // Staff Provisioning (OC only)
    Route::get('/staff', [StaffProvisioningController::class, 'index'])
        ->name('staff.index');
    Route::get('/staff/create', [StaffProvisioningController::class, 'create'])
        ->name('staff.create');
    Route::post('/staff', [StaffProvisioningController::class, 'store'])
        ->name('staff.store');

    // Coordinator Password Reset
    Route::post('/users/{user}/reset-password', [PasswordResetController::class, 'reset'])
        ->name('users.reset-password');

    // Archive Center
    Route::get('/archive', [ArchiveController::class, 'index'])
        ->name('archive.index');
    Route::post('/archive/{type}/{id}/restore', [ArchiveController::class, 'restore'])
        ->name('archive.restore');

    // System Audit Trail
    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index');
});

// Document Upload & Download Routes
Route::middleware('auth')->group(function () {
    Route::post('/documents/uploads', [DocumentController::class, 'requestSlots'])
        ->name('documents.slots');
    Route::post('/documents/uploads/{batch}/confirm', [DocumentController::class, 'confirmBatch'])
        ->name('documents.confirm');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download');
});

// Local / Test Signed Storage Endpoints (Mocks S3 presigned operations)
Route::put('/documents/upload/direct', [DocumentController::class, 'directUpload'])
    ->name('documents.upload.direct');
Route::get('/documents/{document}/serve', [DocumentController::class, 'directDownloadServe'])
    ->name('documents.download.serve');

require __DIR__.'/auth.php';

