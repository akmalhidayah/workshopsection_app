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
        Schema::create('lhpp_approval_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('notification_number');     // rel ke LHPP.notification_number
            $table->string('sign_type');               // contoh: manager_pkm
            $table->unsignedBigInteger('user_id');     // user yg dituju

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();

            $table->unsignedBigInteger('issued_by')->nullable();   // siapa yg issue
            $table->string('issued_channel')->nullable();          // mis: 'system'
            $table->string('ip_issued')->nullable();
            $table->string('ua_issued')->nullable();

            $table->timestamps();

            // index biar query cepat
            $table->index(['notification_number', 'sign_type']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lhpp_approval_tokens');
    }
};
