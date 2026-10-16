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
        Schema::create('company_verification_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('verification_requirement_id')->constrained('verification_requirements')->cascadeOnDelete();
            $table->string('status')->default('missing'); // missing, submitted, accepted, rejected
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->text('remark')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'verification_requirement_id'], 'company_req_unique');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_verification_items');
    }
};
