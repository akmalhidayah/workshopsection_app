<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLpjsTable extends Migration
{
    public function up()
    {
        Schema::create('lpjs', function (Blueprint $table) {
            $table->string('notification_number')->primary(); // Menjadikan notification_number sebagai primary key
            $table->string('lpj_number'); // Nomor LPJ
            $table->string('lpj_document_path'); // Path dokumen LPJ
            $table->string('ppl_number')->nullable(); // Nomor PPL
            $table->string('ppl_document_path')->nullable(); // Path dokumen PPL

            // Kolom tambahan untuk pembayaran termin
            $table->enum('termin1', ['belum', 'sudah'])->default('belum')->comment('Status pembayaran termin pertama');
            $table->enum('termin2', ['belum', 'sudah'])->default('belum')->comment('Status pembayaran termin kedua');

            // -----------------------
            // Kolom garansi (sederhana)
            // Hanya menyimpan jumlah bulan (1..12). Nullable agar backward-compatible.
            $table->unsignedTinyInteger('garansi_months')->nullable()->comment('Masa garansi dalam bulan (1..12). Pilihan dropdown');
            // Opsional label/catatan singkat
            $table->string('garansi_label')->nullable()->comment('Keterangan/catatan garansi (opsional)');
            // -----------------------

            $table->timestamp('update_date')->nullable(); // Kolom update date
            $table->timestamps(); // created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('lpjs');
    }
}
