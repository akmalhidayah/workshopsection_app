<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpksTable extends Migration
{
    public function up()
    {
        Schema::create('spks', function (Blueprint $table) {
            $table->string('nomor_spk')->primary(); // Nomor SPK menjadi primary key
            $table->string('perihal');
            $table->date('tanggal_spk');
            $table->string('notification_number'); // Nomor notifikasi
            $table->string('unit_work'); // Unit kerja
            $table->string('keterangan_pekerjaan')->nullable(); // Keterangan pengerjaan bisa nullable
            $table->json('functional_location')->nullable(); // Functional Location bisa nullable
            $table->json('scope_pekerjaan')->nullable(); // Scope Pekerjaan bisa nullable
            $table->json('qty')->nullable(); // Qty bisa nullable
            $table->json('stn')->nullable(); // Satuan bisa nullable
            $table->json('keterangan')->nullable(); // Keterangan bisa nullable
            $table->text('manager_signature')->nullable(); // Tanda tangan Manager
            $table->text('senior_manager_signature')->nullable(); // Tanda tangan Senior Manager
            $table->timestamps();
        });

        // Menambahkan primary key ke tabel notifications hanya jika belum ada
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'notification_number')) {
                $table->string('notification_number')->primary()->change(); // Mengubah notification_number menjadi primary key
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('spks');

        // Mengembalikan perubahan di tabel notifications
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'notification_number')) {
                $table->dropPrimary(['notification_number']);
            }
        });
    }
}


