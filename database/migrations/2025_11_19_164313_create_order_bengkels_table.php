<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_bengkels', function (Blueprint $table) {
            // notification_number menjadi primary key (string)
            $table->string('notification_number')->primary();

            // status anggaran: Tersedia / Tidak Tersedia / Menunggu
            $table->string('konfirmasi_anggaran')->nullable();
            $table->text('keterangan_konfirmasi')->nullable();
            $table->string('status_anggaran')->nullable()->default('Menunggu');
            $table->text('keterangan_anggaran')->nullable();

             // ✅ E-KORIN (baru)
            $table->string('nomor_e_korin')
                  ->nullable()
                  ->unique()
                  ->comment('Nomor E-KORIN (bila ada)');
            $table->enum('status_e_korin', ['waiting_approval','waiting_korin', 'waiting_transfer', 'complete_transfer'])
                  ->nullable()
                  ->comment('Status E-KORIN: waiting_korin / waiting_transfer / complete_transfer');


            // status material: belum / ready / partial (sesuaikan)
            $table->string('status_material')->nullable()->default('belum');
            $table->text('keterangan_material')->nullable();

            // progress pekerjaan: not_started / in_progress / done
            $table->string('progress_status')->nullable()->default('not_started');
            $table->text('keterangan_progress')->nullable();

            // catatan umum (sama seperti notification->catatan, bisa duplikat)
            $table->text('catatan')->nullable();

            $table->timestamps();

            // FK -> notifications(notification_number)
            $table->foreign('notification_number')
                  ->references('notification_number')
                  ->on('notifications')
                  ->onDelete('cascade'); // hapus bila notification dihapus
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_bengkels');
    }
};
