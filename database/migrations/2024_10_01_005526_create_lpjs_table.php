<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLpjsTable extends Migration
{
    public function up()
    {
        // Hapus tabel lama bila ada (sesuai permintaan: replace toàn table)
        Schema::dropIfExists('lpjs');

        Schema::create('lpjs', function (Blueprint $table) {
            // Primary
            $table->string('notification_number')->primary();

            // --- Nomor per-termin (LPJ & PPL) ---
            $table->string('lpj_number_termin1')->nullable()->comment('Nomor LPJ termin 1');
            $table->string('ppl_number_termin1')->nullable()->comment('Nomor PPL termin 1');

            $table->string('lpj_number_termin2')->nullable()->comment('Nomor LPJ termin 2');
            $table->string('ppl_number_termin2')->nullable()->comment('Nomor PPL termin 2');

            // --- File path per-termin (LPJ & PPL) ---
            $table->string('lpj_document_path_termin1')->nullable()->comment('Path dokumen LPJ termin 1');
            $table->string('ppl_document_path_termin1')->nullable()->comment('Path dokumen PPL termin 1');

            $table->string('lpj_document_path_termin2')->nullable()->comment('Path dokumen LPJ termin 2');
            $table->string('ppl_document_path_termin2')->nullable()->comment('Path dokumen PPL termin 2');

            // --- Status pembayaran per termin ---
            $table->enum('termin1', ['belum', 'sudah'])->default('belum')->comment('Status pembayaran termin 1');
            $table->enum('termin2', ['belum', 'sudah'])->default('belum')->comment('Status pembayaran termin 2');


            // --- Meta ---
            $table->timestamp('update_date')->nullable()->comment('Waktu update terakhir');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lpjs');
    }
}
