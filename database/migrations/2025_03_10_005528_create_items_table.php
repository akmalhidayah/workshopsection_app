<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->string('notification_number')->primary(); // Menggunakan notification_number sebagai primary key
            $table->string('deskripsi_pekerjaan'); // Diambil dari Abnormalitas
            $table->bigInteger('total_hpp');
            $table->json('materials'); // Array untuk menyimpan Material & Jasa
            $table->json('harga'); // Harga dari masing-masing material & jasa
            $table->bigInteger('total')->nullable();
            $table->decimal('total_margin', 15, 2)->nullable(); // Total margin
            $table->string('approved_by')->nullable(); // Yang menyetujui
            $table->timestamps();

            // Foreign key ke notification
            $table->foreign('notification_number')->references('notification_number')->on('notifications')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
};

