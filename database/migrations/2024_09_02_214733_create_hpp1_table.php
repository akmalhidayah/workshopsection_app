<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hpp1', function (Blueprint $table) {
            // PK: 1 notifikasi = 1 HPP
            $table->string('notification_number')->primary();

            // Header fields
            $table->string('cost_centre')->nullable();
            $table->text('description')->nullable();
            $table->string('requesting_unit')->nullable();
            $table->string('controlling_unit')->nullable();
            $table->string('outline_agreement')->nullable();

            // Line-arrays disimpan sebagai JSON (panjang harus sinkron di sisi controller)
            $table->json('jenis_item')->nullable();
            $table->json('nama_item')->nullable();
            $table->json('jumlah_item')->nullable();
            $table->json('qty')->nullable();
            $table->json('satuan')->nullable();
            $table->json('harga_satuan')->nullable();

            $table->json('harga_total')->nullable();
            $table->json('keterangan')->nullable();

            // Total & status
            $table->decimal('total_amount', 20, 2)->nullable();
            $table->string('status', 32)->default('draft'); // draft|submitted|approved_*|rejected

            // Sumber & catatan
            $table->string('source_form')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->text('controlling_notes')->nullable();
            $table->text('requesting_notes')->nullable();

            // Tanda tangan: simpan PATH file (bukan base64) agar DB ringan
            $table->string('manager_signature', 255)->nullable();
            $table->string('senior_manager_signature', 255)->nullable();
            $table->string('general_manager_signature', 255)->nullable();
            $table->string('director_signature', 255)->nullable();

            // (opsional) signature dari sisi requesting unit, jika memang diperlukan
            $table->string('manager_signature_requesting_unit', 255)->nullable();
            $table->string('senior_manager_signature_requesting_unit', 255)->nullable();
            $table->string('general_manager_signature_requesting_unit', 255)->nullable();

            // User penanda tangan
            $table->unsignedBigInteger('manager_signature_user_id')->nullable();
            $table->unsignedBigInteger('senior_manager_signature_user_id')->nullable();
            $table->unsignedBigInteger('general_manager_signature_user_id')->nullable();
            $table->unsignedBigInteger('director_signature_user_id')->nullable();
            $table->unsignedBigInteger('manager_signature_requesting_user_id')->nullable();
            $table->unsignedBigInteger('senior_manager_signature_requesting_user_id')->nullable();
            $table->unsignedBigInteger('general_manager_signature_requesting_user_id')->nullable();

            // FK ke users
            $table->foreign('manager_signature_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('senior_manager_signature_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('general_manager_signature_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('director_signature_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('manager_signature_requesting_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('senior_manager_signature_requesting_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('general_manager_signature_requesting_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            
            $table->timestamp('manager_signed_at')->nullable();
            $table->timestamp('senior_manager_signed_at')->nullable();
            $table->timestamp('general_manager_signed_at')->nullable();
            $table->timestamp('director_signed_at')->nullable();

            // peminta
            $table->timestamp('manager_requesting_signed_at')->nullable();
            $table->timestamp('senior_manager_requesting_signed_at')->nullable();
            $table->timestamp('general_manager_requesting_signed_at')->nullable();
            $table->timestamps();

            // Index untuk query umum
            $table->index('outline_agreement', 'hpp1_outline_agreement_idx');
            $table->index('requesting_unit', 'hpp1_requesting_unit_idx');
            $table->index('controlling_unit', 'hpp1_controlling_unit_idx');
            $table->index('status', 'hpp1_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hpp1');
    }
};
