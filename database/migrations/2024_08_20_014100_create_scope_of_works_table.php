<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('scope_of_works', function (Blueprint $table) {
            $table->string('notification_number')->primary();
            $table->string('nama_pekerjaan');
            $table->string('unit_kerja');
            $table->date('tanggal_pemakaian')->nullable();
            $table->date('tanggal_dokumen');
            $table->json('scope_pekerjaan'); // Menyimpan scope pekerjaan sebagai JSON
            $table->json('qty'); // Menyimpan quantity sebagai JSON
            $table->json('satuan'); // Menyimpan satuan sebagai JSON
            $table->json('keterangan')->nullable();
            $table->text('catatan');
            $table->string('nama_penginput')->nullable();
            $table->text('tanda_tangan')->nullable(); // Menambahkan kolom tanda tangan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scope_of_works');
    }
};

