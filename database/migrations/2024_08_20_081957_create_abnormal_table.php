<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('abnormals', function (Blueprint $table) {
            $table->string('notification_number')->primary(); // Menggunakan notification_number sebagai primary key
            $table->string('abnormal_title');
            $table->string('unit_kerja');
            $table->date('abnormal_date');
            $table->text('problem_description');
            $table->text('root_cause');
            $table->text('immediate_actions');
            $table->text('summary');
            $table->text('manager_signature')->nullable();
            $table->text('senior_manager_signature')->nullable();      
            
            // Menambahkan kolom user_id untuk mengidentifikasi pengguna yang menandatangani
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('manager_signature_user_id')->nullable();
            $table->unsignedBigInteger('senior_manager_signature_user_id')->nullable();
        
            $table->foreign('manager_signature_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('senior_manager_signature_user_id')->references('id')->on('users')->onDelete('set null');
            
            
            // Menggunakan JSON untuk menyimpan actions, risks, dan files
            $table->json('actions')->nullable();
            $table->json('risks')->nullable();
            $table->json('files')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abnormals');
        $table->dropColumn('manager_signature');
        $table->dropColumn('senior_manager_signature');
    }
};

