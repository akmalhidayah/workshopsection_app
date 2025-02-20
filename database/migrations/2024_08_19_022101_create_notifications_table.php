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
        Schema::create('notifications', function (Blueprint $table) {
            $table->string('notification_number')->primary(); // Menjadikan notification_number sebagai primary key
            $table->string('job_name');
            $table->string('unit_work');
            $table->date('input_date');
            $table->string('status')->default('Pending');
            $table->string('jenis_kontrak')->nullable();  
            $table->string('nama_kontrak')->nullable(); 
            $table->string('priority'); 
            $table->string('status_anggaran')->default('Tersedia'); // Kolom baru untuk status verifikasi anggaran
            $table->timestamp('update_date')->nullable();
            $table->date('usage_plan_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('update_date'); // Menghapus kolom jika rollback
        });
    }
};

