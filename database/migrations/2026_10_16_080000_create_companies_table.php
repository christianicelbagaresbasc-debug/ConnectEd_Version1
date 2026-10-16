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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('classification')->default('new_company'); // new_company, existing_partner
            $table->string('verification_status')->default('pending'); // pending, under_review, verified, rejected, revoked
            $table->boolean('is_active')->default(true);
            $table->string('industry')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->text('bsit_relevance_note')->nullable();
            $table->string('source')->default('oc_created'); // oc_created, student_suggestion
            $table->foreignId('promoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('promoted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['classification', 'verification_status']);
            $table->index('is_active');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
