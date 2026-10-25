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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->restrictOnDelete();
            $table->foreignId('opportunity_id')->constrained('opportunities')->restrictOnDelete();
            $table->string('status')->default('pending'); // pending, under_review, accepted, rejected, withdrawn, declined, placed
            $table->text('cover_note')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->foreignId('decided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_remark')->nullable();
            $table->timestamp('student_confirmed_at')->nullable();
            $table->timestamp('oc_confirmed_at')->nullable();
            $table->string('withdrawn_reason')->nullable();
            $table->string('school_year')->default('2026-2027');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_profile_id', 'status']);
            $table->index(['opportunity_id', 'status']);
            $table->index('school_year');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS applications_student_opp_unique ON applications (student_profile_id, opportunity_id) WHERE deleted_at IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
