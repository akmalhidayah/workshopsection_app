<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('gambar_tekniks', function (Blueprint $table) {
            $table->string('notification_number')->primary(); // Menggunakan notification_number sebagai primary key
            $table->foreign('notification_number')
                  ->references('notification_number')
                  ->on('notifications')
                  ->onDelete('cascade'); // Deletes related gambar_teknik if notification is deleted
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gambar_tekniks');
    }
};



