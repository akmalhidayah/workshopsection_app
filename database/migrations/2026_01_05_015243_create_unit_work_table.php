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
        Schema::create('unit_work', function (Blueprint $table) {
            $table->id();

            // RELASI KE DEPARTMENT (unit ini berada di departemen mana)
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained('departments')
                  ->nullOnDelete();

            // User yang menjabat sebagai Senior Manager untuk unit work ini
            $table->foreignId('senior_manager_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // LOGIC LAMA (TIDAK DIUBAH)
            $table->string('name')->unique();
            $table->json('seksi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_work');
    }
};
