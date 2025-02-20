<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKuotaAnggaranOaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kuota_anggaran_oa', function (Blueprint $table) {
            $table->string('outline_agreement')->primary(); // Jadikan Outline Agreement sebagai primary key
            $table->string('unit_work')->default('Workshop & Construction'); // Default value
            $table->string('jenis_kontrak'); // Jenis Kontrak
            $table->string('nama_kontrak'); // Nama Kontrak
            $table->decimal('nilai_kontrak', 15, 2); // Nilai Kontrak
            $table->decimal('tambahan_kuota_kontrak', 15, 2)->nullable(); // Tambahan Kuota Kontrak (Opsional)
            $table->decimal('total_kuota_kontrak', 15, 2); // Total Kuota Kontrak (hasil dari nilai_kontrak + tambahan_kuota_kontrak)
            $table->date('periode_kontrak_start'); // Tanggal Mulai Kontrak
            $table->date('periode_kontrak_end'); // Tanggal Akhir Kontrak
            $table->date('adendum_end')->nullable(); // Tanggal Akhir Adendum (Opsional)
            $table->date('periode_kontrak_final')->nullable(); // Tanggal akhir kontrak termasuk adendum jika ada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kuota_anggaran_oa');
    }
}
