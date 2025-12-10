<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('garansis', function (Blueprint $table) {
            $table->id();

            $table->string('notification_number')->index();

            // lama garansi dalam bulan (boleh 0)
            $table->integer('garansi_months')->nullable();

            // tanggal mulai & tanggal akhir garansi
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // status garansi (optional)
            $table->enum('status', ['belum_dimulai', 'masih_berlaku', 'habis'])
                  ->default('belum_dimulai');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('garansis');
    }
};
