<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();

            // Target penerima notifikasi
            $table->string('target_role', 50)->index(); 
            // contoh: admin, pkm, approval

            // Konteks entity
            $table->string('entity_type', 50)->index();
            // contoh: notification, hpp, spk, lhpp

            $table->string('entity_id')->index();
            // biasanya notification_number

            // Jenis aksi
            $table->string('action', 50);
            // contoh: created, signed, approved

            // Konten UI
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url');

            // Prioritas visual
            $table->enum('priority', ['high', 'normal'])->default('normal');

            // Status baca
            $table->boolean('is_read')->default(false)->index();

            // Timestamp event
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
