<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hpp1', function (Blueprint $table) {
            $table->string('notification_number')->primary();
            $table->string('cost_centre')->nullable();
            $table->text('description')->nullable();
            $table->string('completion_target')->nullable();
            $table->string('usage_plan')->nullable();
            $table->string('requesting_unit')->nullable();
            $table->string('controlling_unit')->nullable();
            $table->string('outline_agreement')->nullable();
            $table->json('uraian_pekerjaan')->nullable();
            $table->json('jenis_material')->nullable();
            $table->json('qty')->nullable();
            $table->json('satuan')->nullable();
            $table->json('volume_satuan')->nullable();
            $table->json('jumlah_volume_satuan')->nullable();
            $table->json('harga_material')->nullable();
            $table->json('harga_consumable')->nullable();
            $table->json('harga_upah')->nullable();
            $table->json('jumlah_harga_material')->nullable();
            $table->json('jumlah_harga_consumable')->nullable();
            $table->json('jumlah_harga_upah')->nullable();
            $table->json('harga_total')->nullable();
            $table->json('keterangan')->nullable();
            $table->decimal('total_amount', 20, 2)->nullable();
            $table->string('source_form')->nullable();
            $table->string('rejection_reason')->nullable(); // Kolom untuk menyimpan alasan penolakan


            // Kolom untuk menyimpan tanda tangan dalam bentuk Base64
            $table->text('manager_signature')->nullable();
            $table->text('senior_manager_signature')->nullable();
            $table->text('general_manager_signature')->nullable();
            $table->text('director_signature')->nullable();
            $table->text('manager_signature_requesting_unit')->nullable();
            $table->text('senior_manager_signature_requesting_unit')->nullable();
            $table->text('general_manager_signature_requesting_unit')->nullable();
            $table->text('controlling_notes')->nullable(); // Kolom untuk catatan fungsi pengendali
            $table->text('requesting_notes')->nullable(); // Kolom untuk catatan fungsi peminta

            // Menambahkan kolom untuk menyimpan user_id yang bertanda tangan
            $table->unsignedBigInteger('manager_signature_user_id')->nullable();
            $table->unsignedBigInteger('senior_manager_signature_user_id')->nullable();
            $table->unsignedBigInteger('general_manager_signature_user_id')->nullable();
            $table->unsignedBigInteger('director_signature_user_id')->nullable();
            $table->unsignedBigInteger('manager_signature_requesting_user_id')->nullable();
            $table->unsignedBigInteger('senior_manager_signature_requesting_user_id')->nullable();
            $table->unsignedBigInteger('general_manager_signature_requesting_user_id')->nullable();

            // Menambahkan foreign key untuk relasi dengan tabel users
            $table->foreign('manager_signature_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('senior_manager_signature_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('general_manager_signature_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('director_signature_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_signature_requesting_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('senior_manager_signature_requesting_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('general_manager_signature_requesting_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hpp1');
    }
};
