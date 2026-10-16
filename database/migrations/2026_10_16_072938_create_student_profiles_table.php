<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('student_number')->unique();
            $table->string('program')->default('BSIT');
            $table->string('year_level')->default('4th Year');
            $table->string('section')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('address')->nullable();
            $table->text('bio')->nullable();
            $table->foreignId('resume_document_id')->nullable()->constrained('documents')->restrictOnDelete();
            $table->string('portfolio_url')->nullable();
            $table->foreignId('assigned_coc_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('school_year')->default('2026-2027');
            $table->boolean('has_active_placement')->default(false);
            $table->timestamp('consented_at')->nullable();
            $table->string('privacy_notice_version')->default('v1.0-draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index('assigned_coc_id');
            $table->index('school_year');
            $table->index('has_active_placement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
