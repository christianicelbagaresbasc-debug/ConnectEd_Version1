<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->restrictOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities')->restrictOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->restrictOnDelete();
            $table->string('status')->default('placed'); // placed, authorized, ongoing, completed, terminated
            $table->boolean('is_active')->default(true); // true for placed, authorized, ongoing
            $table->timestamp('start_authorized_at')->nullable();
            $table->text('start_authorization_note')->nullable();
            $table->foreignId('start_authorization_document_id')->nullable()->constrained('documents')->restrictOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->foreignId('confirmed_by')->constrained('users')->restrictOnDelete();
            $table->string('school_year')->default('2026-2027');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_profile_id', 'is_active']);
            $table->index('status');
            $table->index('company_id');
            $table->index('school_year');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS placements_student_active_unique ON placements (student_profile_id) WHERE is_active = true AND deleted_at IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placements');
    }
};
