<?php

use App\Http\Controllers\Auth\ForcedPasswordChangeController;
use App\Http\Controllers\Coordinator\ArchiveController;
use App\Http\Controllers\Coordinator\AuditLogController;
use App\Http\Controllers\Coordinator\CompanyController;
use App\Http\Controllers\Coordinator\CompanySuggestionController;
use App\Http\Controllers\Coordinator\CompanyVerificationController;
use App\Http\Controllers\Coordinator\PasswordResetController;
use App\Http\Controllers\Coordinator\StaffProvisioningController;
use App\Http\Controllers\Coordinator\StudentApprovalController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\StudentCompanyController;
use App\Http\Controllers\Student\StudentCompanySuggestionController;
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

    // Company Records Management (F-006)
    Route::get('/companies', [CompanyController::class, 'index'])
        ->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])
        ->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])
        ->name('companies.store');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])
        ->name('companies.show');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])
        ->name('companies.edit');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])
        ->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])
        ->name('companies.destroy');
    Route::post('/companies/{company}/promote', [CompanyController::class, 'promote'])
        ->name('companies.promote');

    // 6-Point Company Verification Checklist (F-007)
    Route::get('/companies/{company}/verification', [CompanyVerificationController::class, 'show'])
        ->name('companies.verification');
    Route::post('/companies/{company}/verification/items/{item}', [CompanyVerificationController::class, 'updateItem'])
        ->name('companies.verification.items.update');
    Route::post('/companies/{company}/verification/status', [CompanyVerificationController::class, 'updateStatus'])
        ->name('companies.verification.status.update');

    // Student Company Suggestions Queue (F-008)
    Route::get('/company-suggestions', [CompanySuggestionController::class, 'index'])
        ->name('suggestions.index');
    Route::post('/company-suggestions/{suggestion}/convert', [CompanySuggestionController::class, 'convert'])
        ->name('suggestions.convert');
    Route::post('/company-suggestions/{suggestion}/dismiss', [CompanySuggestionController::class, 'dismiss'])
        ->name('suggestions.dismiss');
});

// Student Module Routes
Route::middleware(['auth', 'verified'])->prefix('student')->name('student.')->group(function () {
    // Partner Companies Directory (Verified & Existing Only)
    Route::get('/companies', [StudentCompanyController::class, 'index'])
        ->name('companies.index');
    Route::get('/companies/{company}', [StudentCompanyController::class, 'show'])
        ->name('companies.show');

    // Suggest a Company (F-008)
    Route::get('/company-suggestions', [StudentCompanySuggestionController::class, 'index'])
        ->name('suggestions.index');
    Route::get('/company-suggestions/create', [StudentCompanySuggestionController::class, 'create'])
        ->name('suggestions.create');
    Route::post('/company-suggestions', [StudentCompanySuggestionController::class, 'store'])
        ->name('suggestions.store');
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

