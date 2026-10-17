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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('qualifications')->nullable();
            $table->unsignedInteger('slots_total')->default(1);
            $table->unsignedInteger('slots_filled')->default(0);
            $table->string('location')->nullable();
            $table->string('work_arrangement')->default('onsite'); // onsite, remote, hybrid
            $table->date('deadline')->nullable();
            $table->string('status')->default('draft'); // draft, pending_approval, open, closed, archived
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('school_year')->default('2026-2027');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'deadline']);
            $table->index('company_id');
            $table->index('school_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
