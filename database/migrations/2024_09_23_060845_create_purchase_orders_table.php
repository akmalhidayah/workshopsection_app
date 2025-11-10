<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->string('notification_number')->primary(); // Primary key (relasi ke notifications)
            $table->string('purchase_order_number')->nullable(); // Nomor PO
            $table->string('po_document_path')->nullable(); // Path dokumen PO (PDF, DOC, XLSX, JPG, PNG)
            
            // ✅ Approval status
            $table->boolean('approve_manager')->default(false);
            $table->boolean('approve_senior_manager')->default(false);
            $table->boolean('approve_general_manager')->default(false);
            $table->boolean('approve_direktur_operasional')->default(false);
            
            // ✅ Progress & tracking
            $table->integer('progress_pekerjaan')->default(0);
            $table->date('target_penyelesaian')->nullable();
            $table->timestamp('update_date')->nullable();

            // ✅ Approval status & notes
            $table->string('approval_target')->nullable(); // "setuju" / "tidak_setuju"
            $table->text('approval_note')->nullable();     // Catatan kecil saat setuju/tidak setuju
            
            // ✅ Catatan admin & PKM (catatan dari admin bengkel)
            $table->text('catatan')->nullable();
            $table->text('catatan_pkm')->nullable();

            $table->timestamps();

            // Relasi ke tabel notifications
            $table->foreign('notification_number')
                  ->references('notification_number')
                  ->on('notifications')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
