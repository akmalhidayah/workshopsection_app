<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_anggarans', function (Blueprint $table) {
            $table->id();

            // Relasi ke notifikasi berdasarkan nomor notifikasi (unik)
            $table->string('notification_number')->unique();

            // Status verifikasi anggaran (default: Menunggu)
            $table->enum('status_anggaran', ['Tersedia', 'Tidak Tersedia', 'Menunggu'])
                  ->default('Menunggu')
                  ->comment('Status hasil verifikasi anggaran');

            // Cost Element (kode biaya SAP)
            $table->string('cost_element')
                  ->nullable()
                  ->comment('Kode cost element SAP atau akun biaya terkait');

            // ✅ Kategori Biaya (tetap)
            $table->enum('kategori_biaya', ['pemeliharaan', 'non pemeliharaan', 'capex'])
                  ->nullable()
                  ->comment('Kategori biaya: pemeliharaan / non pemeliharaan / capex');

            // ✅ Kategori Item (baru) → dropdown: Spare Part / Jasa
            $table->enum('kategori_item', ['spare part', 'jasa'])
                  ->nullable()
                  ->comment('Kategori item pekerjaan: spare part / jasa');

            // ✅ E-KORIN (baru)
            $table->string('nomor_e_korin')
                  ->nullable()
                  ->unique()
                  ->comment('Nomor E-KORIN (bila ada)');
            $table->enum('status_e_korin', ['waiting_korin','waiting_approval', 'waiting_transfer', 'complete_transfer'])
                  ->nullable()
                  ->comment('Status E-KORIN: waiting_korin / waiting_transfer / complete_transfer');

            // Catatan tambahan dari tim verifikasi
            $table->text('catatan')
                  ->nullable()
                  ->comment('Catatan tambahan atau keterangan hasil verifikasi');

            // Tanggal terakhir update status verifikasi
            $table->timestamp('tanggal_verifikasi')
                  ->nullable()
                  ->comment('Tanggal terakhir dilakukan update status verifikasi');

            // Relasi ke tabel notifications berdasarkan nomor notifikasi
            $table->foreign('notification_number')
                  ->references('notification_number')
                  ->on('notifications')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // indeks bantu untuk performa filter
            $table->index('status_anggaran');
            $table->index('status_e_korin');
            $table->index('tanggal_verifikasi');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_anggarans');
    }
};
