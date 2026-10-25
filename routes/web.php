<?php

use App\Http\Controllers\Attorney\MoaLegalReviewController;
use App\Http\Controllers\Auth\ForcedPasswordChangeController;
use App\Http\Controllers\Coordinator\ApplicationController as CoordinatorApplicationController;
use App\Http\Controllers\Coordinator\ArchiveController;
use App\Http\Controllers\Coordinator\AuditLogController;
use App\Http\Controllers\Coordinator\CompanyController;
use App\Http\Controllers\Coordinator\CompanySuggestionController;
use App\Http\Controllers\Coordinator\CompanyVerificationController;
use App\Http\Controllers\Coordinator\PasswordResetController;
use App\Http\Controllers\Coordinator\PlacementController as CoordinatorPlacementController;
use App\Http\Controllers\Coordinator\SkillController;
use App\Http\Controllers\Coordinator\StaffProvisioningController;
use App\Http\Controllers\Coordinator\StudentApprovalController;
use App\Http\Controllers\Coordinator\StudentAssignmentController;
use App\Http\Controllers\Coordinator\StudentController;
use App\Http\Controllers\Coordinator\TaskController;
use App\Http\Controllers\Coordinator\WeeklyReportReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentLibraryController;
use App\Http\Controllers\MoaController;
use App\Http\Controllers\MoaCustodyController;
use App\Http\Controllers\MoaVersionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PartnerCompany\CompanyApplicationController;
use App\Http\Controllers\PartnerCompany\CompanyPlacementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Student\RecommendationController;
use App\Http\Controllers\Student\StudentApplicationController;
use App\Http\Controllers\Student\StudentCertificationController;
use App\Http\Controllers\Student\StudentCompanyController;
use App\Http\Controllers\Student\StudentCompanySuggestionController;
use App\Http\Controllers\Student\StudentPlacementController;
use App\Http\Controllers\Student\StudentSkillController;
use App\Http\Controllers\Student\StudentWeeklyReportController;
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

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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

    // IT Skills Catalog Management (F-011)
    Route::get('/skills', [SkillController::class, 'index'])
        ->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])
        ->name('skills.store');
    Route::put('/skills/{skill}', [SkillController::class, 'update'])
        ->name('skills.update');
    Route::post('/skills/{skill}/approve', [SkillController::class, 'approve'])
        ->name('skills.approve');
    Route::delete('/skills/{skill}', [SkillController::class, 'destroy'])
        ->name('skills.destroy');

    // Student Directory & Cohort Monitoring (FR-03, FR-18)
    Route::get('/students', [StudentController::class, 'index'])
        ->name('students.index');
    Route::get('/students/{studentProfile}', [StudentController::class, 'show'])
        ->name('students.show');
    Route::post('/students/{student}/assign-coc', [StudentAssignmentController::class, 'assign'])
        ->name('students.assign-coc');
    Route::post('/students/bulk-assign-coc', [StudentAssignmentController::class, 'bulkAssign'])
        ->name('students.bulk-assign-coc');

    // Application Management & Offer Decisions (F-014, BR-20)
    Route::get('/applications', [CoordinatorApplicationController::class, 'index'])
        ->name('applications.index');
    Route::get('/applications/{application}', [CoordinatorApplicationController::class, 'show'])
        ->name('applications.show');
    Route::post('/applications/{application}/review', [CoordinatorApplicationController::class, 'markUnderReview'])
        ->name('applications.review');
    Route::post('/applications/{application}/decide', [CoordinatorApplicationController::class, 'decide'])
        ->name('applications.decide');
    Route::post('/applications/{application}/confirm', [CoordinatorApplicationController::class, 'confirm'])
        ->name('applications.confirm');

    // Placement Tracking & Start Authorization (F-015, F-016, BR-19)
    Route::get('/placements', [CoordinatorPlacementController::class, 'index'])
        ->name('placements.index');
    Route::get('/placements/{placement}', [CoordinatorPlacementController::class, 'show'])
        ->name('placements.show');
    Route::post('/placements/{placement}/authorize', [CoordinatorPlacementController::class, 'authorizeStart'])
        ->name('placements.authorize');
    Route::post('/placements/{placement}/status', [CoordinatorPlacementController::class, 'updateStatus'])
        ->name('placements.status');

    // Weekly Report Reviews (F-016, Assumption A9)
    Route::post('/weekly-reports/{weeklyReport}/review', [WeeklyReportReviewController::class, 'review'])
        ->name('weekly-reports.review');
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

    // Student Skills Profile & Suggestion (F-011)
    Route::get('/profile/skills', [StudentSkillController::class, 'index'])
        ->name('profile.skills');
    Route::post('/profile/skills', [StudentSkillController::class, 'store'])
        ->name('profile.skills.store');
    Route::put('/profile/skills/{studentSkill}', [StudentSkillController::class, 'update'])
        ->name('profile.skills.update');
    Route::delete('/profile/skills/{studentSkill}', [StudentSkillController::class, 'destroy'])
        ->name('profile.skills.destroy');
    Route::post('/skills/suggest', [StudentSkillController::class, 'suggest'])
        ->name('skills.suggest');

    // Certifications (FR-03)
    Route::post('/profile/certifications', [StudentCertificationController::class, 'store'])
        ->name('profile.certifications.store');
    Route::delete('/profile/certifications/{certification}', [StudentCertificationController::class, 'destroy'])
        ->name('profile.certifications.destroy');

    // Transparent Recommendations Engine (F-013, BR-20)
    Route::get('/recommendations', [RecommendationController::class, 'index'])
        ->name('recommendations.index');

    // Student Applications & Offer Confirmation (F-014, F-015)
    Route::get('/applications', [StudentApplicationController::class, 'index'])
        ->name('applications.index');
    Route::post('/opportunities/{opportunity}/apply', [StudentApplicationController::class, 'store'])
        ->name('opportunities.apply');
    Route::get('/applications/{application}', [StudentApplicationController::class, 'show'])
        ->name('applications.show');
    Route::post('/applications/{application}/confirm', [StudentApplicationController::class, 'confirm'])
        ->name('applications.confirm');
    Route::post('/applications/{application}/decline', [StudentApplicationController::class, 'decline'])
        ->name('applications.decline');
    Route::post('/applications/{application}/withdraw', [StudentApplicationController::class, 'withdraw'])
        ->name('applications.withdraw');

    // Student Placement & Weekly Reports (F-015, F-016)
    Route::get('/placements/{placement}', [StudentPlacementController::class, 'show'])
        ->name('placements.show');
    Route::post('/placements/{placement}/reports', [StudentWeeklyReportController::class, 'store'])
        ->name('placements.reports.store');
});

// Opportunity Management & Directory (F-012, FR-08, FR-09, FR-10)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/create', [OpportunityController::class, 'create'])->name('opportunities.create');
    Route::post('/opportunities', [OpportunityController::class, 'store'])->name('opportunities.store');
    Route::get('/opportunities/{opportunity}', [OpportunityController::class, 'show'])->name('opportunities.show');
    Route::get('/opportunities/{opportunity}/edit', [OpportunityController::class, 'edit'])->name('opportunities.edit');
    Route::put('/opportunities/{opportunity}', [OpportunityController::class, 'update'])->name('opportunities.update');
    Route::post('/opportunities/{opportunity}/approve', [OpportunityController::class, 'approve'])->name('opportunities.approve');
    Route::delete('/opportunities/{opportunity}', [OpportunityController::class, 'destroy'])->name('opportunities.destroy');
});

// Multi-Party MOA Tracking Routes (F-009)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/moas', [MoaController::class, 'index'])->name('moas.index');
    Route::get('/moas/create', [MoaController::class, 'create'])->name('moas.create');
    Route::post('/moas', [MoaController::class, 'store'])->name('moas.store');
    Route::get('/moas/{moa}', [MoaController::class, 'show'])->name('moas.show');
    Route::get('/moas/{moa}/edit', [MoaController::class, 'edit'])->name('moas.edit');
    Route::put('/moas/{moa}', [MoaController::class, 'update'])->name('moas.update');
    Route::delete('/moas/{moa}', [MoaController::class, 'destroy'])->name('moas.destroy');
    Route::post('/moas/{moa}/status', [MoaController::class, 'updateStatus'])->name('moas.status.update');

    // Custody Transfers (BR-16)
    Route::post('/moas/{moa}/custody/transfer', [MoaCustodyController::class, 'transfer'])
        ->name('moas.custody.transfer');

    // Document Versions
    Route::post('/moas/{moa}/versions', [MoaVersionController::class, 'store'])
        ->name('moas.versions.store');
});

// University Attorney Legal Review Routes (F-010, BR-18)
Route::middleware(['auth', 'verified'])->prefix('attorney')->name('attorney.')->group(function () {
    Route::get('/moas', [MoaLegalReviewController::class, 'index'])
        ->name('moas.index');
    Route::post('/moas/{moa}/notes', [MoaLegalReviewController::class, 'storeNote'])
        ->name('moas.notes.store');
    Route::match(['post', 'patch'], '/moas/{moa}/notes/{note}/resolve', [MoaLegalReviewController::class, 'resolveNote'])
        ->name('moas.notes.resolve');
    Route::post('/moas/{moa}/clear', [MoaLegalReviewController::class, 'clearReview'])
        ->name('moas.clear');
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

// Partner Company Module Routes (F-014, F-016, BR-20)

Route::middleware(['auth', 'verified'])->prefix('company')->name('company.')->group(function () {
    Route::get('/applications', [CompanyApplicationController::class, 'index'])
        ->name('applications.index');
    Route::get('/applications/{application}', [CompanyApplicationController::class, 'show'])
        ->name('applications.show');
    Route::post('/applications/{application}/review', [CompanyApplicationController::class, 'markUnderReview'])
        ->name('applications.review');
    Route::post('/applications/{application}/decide', [CompanyApplicationController::class, 'decide'])
        ->name('applications.decide');

    Route::get('/placements', [CompanyPlacementController::class, 'index'])
        ->name('placements.index');
    Route::get('/placements/{placement}', [CompanyPlacementController::class, 'show'])
        ->name('placements.show');
});

// In-System Notifications Center (F-017, BR-02)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
});

// Task Delegation & Workload Management (FR-19)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::match(['put', 'patch'], '/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});

// Centralized Document Library & Compliance Matrix (FR-27, FR-28)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/document-library', [DocumentLibraryController::class, 'index'])
        ->name('document-library.index');
    Route::get('/document-library/completeness', [DocumentLibraryController::class, 'completeness'])
        ->name('document-library.completeness');
    Route::post('/document-library/bundle', [DocumentLibraryController::class, 'downloadBundle'])
        ->name('document-library.bundle');
});

// Institutional OJT Reports & Streaming CSV Exports (FR-18, FR-21)
Route::middleware(['auth', 'verified'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/placements', [ReportController::class, 'placements'])->name('placements');
    Route::get('/placements/csv', [ReportController::class, 'exportPlacementsCsv'])->name('placements.csv');
    Route::get('/companies', [ReportController::class, 'companies'])->name('companies');
    Route::get('/companies/csv', [ReportController::class, 'exportCompaniesCsv'])->name('companies.csv');
    Route::get('/skills', [ReportController::class, 'skills'])->name('skills');
    Route::get('/skills/csv', [ReportController::class, 'exportSkillsCsv'])->name('skills.csv');
});

require __DIR__.'/auth.php';
