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
        Schema::create('unit_work_sections', function (Blueprint $table) {
            $table->id();

            // seksi ini milik unit work mana
            $table->foreignId('unit_work_id')
                  ->constrained('unit_work')
                  ->cascadeOnDelete();

            // nama seksi (misalnya: Produksi, Pemeliharaan, Keuangan)
            $table->string('name');

            // user yang menjabat sebagai Manager seksi (opsional)
            $table->foreignId('manager_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // satu unit tidak boleh punya dua seksi dg nama sama
            $table->unique(['unit_work_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_work_sections');
    }
};
