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
            $table->timestamp('update_date')->nullable(); // Kolom update date, bisa null saat pertama kali di-create
            $table->timestamps(); // Laravel secara otomatis membuat kolom 'created_at' dan 'updated_at'
        });
    }

    public function down()
    {
        Schema::dropIfExists('lpjs');
    }
}


