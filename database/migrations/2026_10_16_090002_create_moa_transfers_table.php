<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: Append-only table, no soft deletes or updated_at.
     */
    public function up(): void
    {
        Schema::create('moa_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moa_id')->constrained('moas')->restrictOnDelete();
            $table->string('from_party')->nullable();
            $table->string('to_party');
            $table->foreignId('from_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['moa_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moa_transfers');
    }
};
