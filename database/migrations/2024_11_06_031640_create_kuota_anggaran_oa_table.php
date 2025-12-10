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
        Schema::create('kuota_anggaran_oa', function (Blueprint $table) {
            // === Identitas OA ===
            $table->string('outline_agreement')->primary(); // OA sebagai primary key
            $table->string('unit_work')->default('Workshop & Construction'); // Unit kerja
            $table->string('jenis_kontrak'); // Jenis Kontrak
            $table->string('nama_kontrak');  // Nama Kontrak

            // === Nilai dan Kuota Kontrak ===
            $table->decimal('nilai_kontrak', 20, 2); // Nilai Kontrak
            $table->decimal('tambahan_kuota_kontrak', 20, 2)->nullable(); // Tambahan Kuota Kontrak (Opsional)
            $table->decimal('total_kuota_kontrak', 20, 2); // Total (nilai_kontrak + tambahan_kuota_kontrak)

            // === Periode Kontrak & Adendum ===
            $table->date('periode_kontrak_start'); // Tanggal Mulai Kontrak
            $table->date('periode_kontrak_end');   // Tanggal Akhir Kontrak
            $table->date('adendum_end')->nullable(); // Akhir Adendum (Opsional)
            $table->date('periode_kontrak_final')->nullable(); // Akhir kontrak final (termasuk adendum)

            // === Target Pemeliharaan (Array JSON) ===
            $table->json('tahun')
                ->nullable()
                ->comment('Daftar tahun untuk target biaya pemeliharaan (array), contoh: [2025,2026]');
            
            $table->json('target_biaya_pemeliharaan')
                ->nullable()
                ->comment('Array nilai target biaya jasa pemeliharaan per tahun, sejajar dengan kolom tahun');

            // === Waktu Buat & Update ===
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuota_anggaran_oa');
    }
};
