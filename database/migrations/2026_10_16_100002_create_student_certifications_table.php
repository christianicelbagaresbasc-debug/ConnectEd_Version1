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
        Schema::create('student_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->restrictOnDelete();
            $table->string('title');
            $table->string('issuer');
            $table->date('issued_at')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_certifications');
    }
};
