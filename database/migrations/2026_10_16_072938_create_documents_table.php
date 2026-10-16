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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->nullableMorphs('documentable');
            $table->string('category'); // resume, certification, moa, verification, authorization, weekly_report, template, other
            $table->string('original_name');
            $table->string('storage_key')->unique();
            $table->string('stored_path');
            $table->string('disk')->default('documents');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum_sha256')->nullable();
            $table->string('validation_status')->default('pending'); // pending, ok, rejected, expired
            $table->text('validation_remark')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('upload_batch_id')->nullable()->constrained('document_batches')->restrictOnDelete();
            $table->string('version_group_id')->nullable();
            $table->unsignedInteger('version_no')->default(1);
            $table->boolean('is_current')->default(true);
            $table->string('review_status')->default('pending_review'); // pending_review, accepted, rejected
            $table->text('review_remark')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('school_year')->default('2026-2027');
            $table->date('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'review_status']);
            $table->index('school_year');
            $table->index(['version_group_id', 'is_current']);
            $table->index('owner_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
