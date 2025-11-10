<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_kawat_las', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();       // contoh: E6013, E7018, SS308
            $table->string('deskripsi')->nullable();    // keterangan tambahan
            $table->string('gambar')->nullable();       // simpan path ke gambar
            $table->unsignedInteger('stok')->default(0); // jumlah stok kawat

            // ✅ tambahan baru
            $table->decimal('harga', 15, 2)->default(0);     // harga per unit kawat
            $table->string('cost_element', 50)->nullable();  // kode cost element

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_kawat_las');
    }
};
