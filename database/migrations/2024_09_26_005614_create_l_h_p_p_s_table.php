<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLhppsTable extends Migration
{
    public function up()
    {
        Schema::create('lhpp', function (Blueprint $table) {
            $table->string('notification_number')->primary(); // Primary Key
            $table->string('nomor_order')->nullable();
            $table->text('description_notifikasi')->nullable();
            $table->string('purchase_order_number');
            $table->string('unit_kerja');
            $table->date('tanggal_selesai');
            $table->integer('waktu_pengerjaan');

            // ======================
            // A. MATERIAL (JSON)
            // ======================
            $table->json('material_description')->nullable();
            $table->json('material_volume')->nullable();
            $table->json('material_harga_satuan')->nullable();
            $table->json('material_jumlah')->nullable();

            // ======================
            // C. UPAH (JSON)
            // ======================
            $table->json('upah_description')->nullable();
            $table->json('upah_volume')->nullable();
            $table->json('upah_harga_satuan')->nullable();
            $table->json('upah_jumlah')->nullable();

            // ======================
            // SUBTOTAL + TOTAL
            // ======================
            $table->decimal('material_subtotal', 15, 2)->nullable();
            $table->decimal('upah_subtotal', 15, 2)->nullable();

            // Total biaya (A + C saja, karena B dihapus)
            $table->decimal('total_biaya', 15, 2)->nullable();

            $table->string('rejection_reason')->nullable(); 
            $table->json('images')->nullable();

            // Kontrak PKM
            $table->enum('kontrak_pkm', ['Fabrikasi', 'Konstruksi', 'Pengerjaan Mesin'])->nullable();

            // Tanda tangan
            $table->text('manager_signature')->nullable();
            $table->text('manager_signature_requesting')->nullable();
            $table->text('manager_pkm_signature')->nullable();
            $table->timestamp('manager_signature_at')->nullable();
            $table->timestamp('manager_signature_requesting_at')->nullable();
            $table->timestamp('manager_pkm_signature_at')->nullable();

            // Catatan
            $table->text('controlling_notes')->nullable();
            $table->text('requesting_notes')->nullable();

            // User ID tanda tangan
            $table->unsignedBigInteger('manager_signature_user_id')->nullable();
            $table->unsignedBigInteger('manager_signature_requesting_user_id')->nullable();
            $table->unsignedBigInteger('manager_pkm_signature_user_id')->nullable();

            $table->foreign('manager_signature_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_signature_requesting_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_pkm_signature_user_id')->references('id')->on('users')->onDelete('cascade');

            // Status Approval
            $table->enum('status_approve', ['Pending', 'Approved', 'Rejected'])->default('Pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lhpp');
    }
}
