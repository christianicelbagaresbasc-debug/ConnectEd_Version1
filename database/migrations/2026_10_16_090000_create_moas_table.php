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
        Schema::create('moas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('student_profile_id')->nullable()->constrained('student_profiles')->restrictOnDelete();
            $table->string('moa_number')->nullable()->unique();
            $table->string('type')->default('university_company');
            $table->string('status')->default('draft');
            $table->string('current_custodian_party')->default('oc');
            $table->foreignId('current_custodian_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('summary')->nullable();
            $table->string('school_year')->default('2026-2027');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['current_custodian_party']);
            $table->index(['student_profile_id']);
            $table->index(['school_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moas');
    }
};
