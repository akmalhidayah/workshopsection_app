<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawat_las', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->date('tanggal');
            $table->string('unit_work', 100);
            $table->string('seksi', 100);

            // ✅ Status order: Waiting Budget / Good Issue
            $table->enum('status', ['Waiting Budget', 'Good Issue'])
                  ->default('Waiting Budget')
                  ->comment('Status progres order kawat las');

            // ✅ Catatan tambahan dari admin/user
            $table->text('catatan')->nullable()
                  ->comment('Keterangan atau catatan tambahan untuk order kawat las');

            // Relasi user
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::create('kawat_las_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kawat_las_id')->constrained('kawat_las')->onDelete('cascade');
            $table->string('jenis_kawat', 50);
            $table->integer('jumlah');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawat_las_details');
        Schema::dropIfExists('kawat_las');
    }
};
