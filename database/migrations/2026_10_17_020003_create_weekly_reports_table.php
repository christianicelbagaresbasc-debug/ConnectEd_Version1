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
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('placements')->restrictOnDelete();
            $table->date('week_start');
            $table->foreignId('document_id')->constrained('documents')->restrictOnDelete();
            $table->string('status')->default('submitted'); // submitted, reviewed, flagged
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['placement_id', 'week_start']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
