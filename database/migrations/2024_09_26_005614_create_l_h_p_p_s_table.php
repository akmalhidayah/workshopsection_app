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
            $table->string('nomor_order'); // Nomor Order
            $table->text('description_notifikasi')->nullable(); // Deskripsi Notifikasi
            $table->string('purchase_order_number'); // Purchasing Order
            $table->string('unit_kerja'); // Unit Kerja Peminta
            $table->date('tanggal_selesai'); // Tanggal Selesai Pekerjaan
            $table->integer('waktu_pengerjaan'); // Waktu Pengerjaan (Hari)

            // Fields for material, consumable, and upah as JSON
            $table->json('material_description')->nullable();
            $table->json('material_volume')->nullable();
            $table->json('material_harga_satuan')->nullable();
            $table->json('material_jumlah')->nullable();
            $table->json('consumable_description')->nullable();
            $table->json('consumable_volume')->nullable();
            $table->json('consumable_harga_satuan')->nullable();
            $table->json('consumable_jumlah')->nullable();
            $table->json('upah_description')->nullable();
            $table->json('upah_volume')->nullable();
            $table->json('upah_harga_satuan')->nullable();
            $table->json('upah_jumlah')->nullable();

            // Subtotals and total biaya
            $table->decimal('material_subtotal', 15, 2)->nullable();
            $table->decimal('consumable_subtotal', 15, 2)->nullable();
            $table->decimal('upah_subtotal', 15, 2)->nullable();
            $table->decimal('total_biaya', 15, 2)->nullable();
            $table->string('rejection_reason')->nullable(); // Kolom untuk menyimpan alasan penolakan
            $table->json('images')->nullable();
            
            // Kontrak PKM
            $table->enum('kontrak_pkm', ['Fabrikasi', 'Konstruksi', 'Pengerjaan Mesin'])->nullable();

            // Tanda Tangan
            $table->text('manager_signature')->nullable();
            $table->text('manager_signature_requesting')->nullable();
            $table->text('manager_pkm_signature')->nullable();

            // Notes
            $table->text('controlling_notes')->nullable(); // Catatan pengendali
            $table->text('requesting_notes')->nullable(); // Catatan peminta

            // Kolom untuk menyimpan user_id yang bertanda tangan
            $table->unsignedBigInteger('manager_signature_user_id')->nullable();
            $table->unsignedBigInteger('manager_signature_requesting_user_id')->nullable();
            $table->unsignedBigInteger('manager_pkm_signature_user_id')->nullable();

            // Foreign key untuk user_id
            $table->foreign('manager_signature_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_signature_requesting_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_pkm_signature_user_id')->references('id')->on('users')->onDelete('cascade');

            // Status Approval
            $table->enum('status_approve', ['Pending', 'Approved', 'Rejected'])->default('Pending'); // Status approval

            $table->timestamps(); // Timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('lhpp');
    }
}
