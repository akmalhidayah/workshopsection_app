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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->string('notification_number')->primary(); // Jadikan notification_number sebagai primary key
            $table->string('purchase_order_number')->nullable(); // Nomor PO
            $table->string('po_document_path')->nullable(); // Path dokumen PO (PDF, Word, Excel, Gambar)
            $table->boolean('approve_manager')->default(false); // Status approve dari Manager
            $table->boolean('approve_senior_manager')->default(false); // Status approve dari Senior Manager
            $table->boolean('approve_general_manager')->default(false); // Status approve dari General Manager
            $table->boolean('approve_direktur_operasional')->default(false); // Status approve dari Direktur Operasional
            $table->integer('progress_pekerjaan')->default(0); // Menambahkan kolom progress_pekerjaan dengan default 0
            $table->text('catatan')->nullable();
            $table->date('target_penyelesaian')->nullable();
            $table->string('approval_target')->nullable();
            $table->timestamp('update_date')->nullable(); // Kolom update date
            $table->timestamps(); // Kolom created_at dan updated_at otomatis

            // Foreign key untuk menghubungkan ke tabel notifications
            $table->foreign('notification_number')->references('notification_number')->on('notifications')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
