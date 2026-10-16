<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Academic Program Scope
    |--------------------------------------------------------------------------
    |
    | Defines the degree programs under the Institute of Computer Studies (ICS)
    | supported by the ConnectEd placement workflow. Defaults to BSIT.
    |
    */
    'allowed_programs' => env('CONNECTED_ALLOWED_PROGRAMS', ['BSIT']),

    /*
    |--------------------------------------------------------------------------
    | Active Academic Year
    |--------------------------------------------------------------------------
    |
    | Used to tag student cohorts, MOA agreements, placements, and reports.
    |
    */
    'current_school_year' => env('CURRENT_SCHOOL_YEAR', '2026-2027'),

    /*
    |--------------------------------------------------------------------------
    | Operational Approval Gates
    |--------------------------------------------------------------------------
    |
    | Determines whether new registrations or company-posted opportunities
    | require formal coordinator approval before becoming active.
    |
    */
    'student_registration_requires_approval' => env('STUDENT_REGISTRATION_REQUIRES_APPROVAL', true),
    'opportunity_requires_approval' => env('OPPORTUNITY_REQUIRES_APPROVAL', true),

    /*
    |--------------------------------------------------------------------------
    | Document Storage and Upload Limits
    |--------------------------------------------------------------------------
    |
    | Enforces single-file, batch, and streaming download constraints.
    |
    */
    'direct_upload' => env('CONNECTED_DIRECT_UPLOAD', true),
    'upload_batch_max' => env('CONNECTED_UPLOAD_BATCH_MAX', 20),
    'max_file_size_mb' => env('CONNECTED_MAX_FILE_SIZE_MB', 10),
    'max_resume_size_mb' => env('CONNECTED_MAX_RESUME_SIZE_MB', 5),
    'bundle_max_files' => env('CONNECTED_BUNDLE_MAX_FILES', 50),
    'bundle_max_mb' => env('CONNECTED_BUNDLE_MAX_MB', 100),

    /*
    |--------------------------------------------------------------------------
    | Privacy & Audit Settings
    |--------------------------------------------------------------------------
    |
    | Under RA 10173 data minimization guidelines, IP logging is disabled by default.
    |
    */
    'audit_ip' => env('CONNECTED_AUDIT_IP', false),
];
