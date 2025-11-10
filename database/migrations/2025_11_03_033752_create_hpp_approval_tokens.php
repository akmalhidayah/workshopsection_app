<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hpp_approval_tokens', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('notification_number');
            $t->string('sign_type', 64); // manager|senior_manager|...|*_requesting
            $t->unsignedBigInteger('user_id');
            $t->timestamp('expires_at');
            $t->timestamp('used_at')->nullable();
            $t->string('issued_by')->nullable();
            $t->string('issued_channel')->nullable();
            $t->string('ip_issued')->nullable();
            $t->string('ua_issued')->nullable();
            $t->timestamps();

            $t->index(['notification_number', 'sign_type']);
            $t->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('hpp_approval_tokens'); }
};
