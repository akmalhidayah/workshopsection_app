<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpksTable extends Migration
{
    public function up()
    {
        Schema::create('spks', function (Blueprint $table) {
            // Nomor SPK sebagai primary key string (sesuai model Anda)
            $table->string('nomor_spk')->primary();

            // Basic fields
            $table->string('perihal');
            $table->date('tanggal_spk')->nullable();
            $table->string('notification_number')->index();
            $table->string('unit_work')->nullable();
            $table->text('keterangan_pekerjaan')->nullable();

            // Arrays json
            $table->json('functional_location')->nullable();
            $table->json('scope_pekerjaan')->nullable();
            $table->json('qty')->nullable();
            $table->json('stn')->nullable();
            $table->json('keterangan')->nullable();

            // Approval / signatures (store base64 or storage path / large text)
            $table->longText('manager_signature')->nullable();
            $table->unsignedBigInteger('manager_signature_user_id')->nullable()->index();
            $table->timestamp('manager_signed_at')->nullable()->index();

            $table->longText('senior_manager_signature')->nullable();
            $table->unsignedBigInteger('senior_manager_signature_user_id')->nullable()->index();
            $table->timestamp('senior_manager_signed_at')->nullable()->index();

            // Tambahan: status singkat (optional)
            $table->string('status')->default('draft')->index(); // draft|submitted|approved|rejected

            $table->timestamps();

            // Foreign key reference (opsional, tambahkan kalau notifications.notification_number memang pk)
            if (Schema::hasTable('notifications')) {
                // gunakan->nullable() di FK tidak perlu karena notification_number required di bisnis lain
                $table->foreign('notification_number')
                      ->references('notification_number')
                      ->on('notifications')
                      ->cascadeOnDelete();
            }
        });
    }

    public function down()
    {
        // drop foreign if exists (guard)
        if (Schema::hasTable('spks')) {
            Schema::table('spks', function (Blueprint $table) {
                // jika FK terdaftar, laravel akan drop otomatis saat drop table
                // tapi aman untuk cek
                if (Schema::hasColumn('spks', 'notification_number')) {
                    // no-op: letting dropIfExists handle it
                }
            });
        }

        Schema::dropIfExists('spks');
    }
}
