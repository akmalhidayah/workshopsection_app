<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpkApprovalTokensTable extends Migration
{
    public function up(): void
    {
        Schema::create('spk_approval_tokens', function (Blueprint $table) {

            // ======================
            // PRIMARY KEY
            // ======================
            $table->uuid('id')->primary();

            // ======================
            // RELASI UTAMA
            // ======================
            $table->string('nomor_spk')->index();               // FK logis ke spks.nomor_spk
            $table->string('notification_number')->index();    // konteks asal (opsional tapi penting)

            // ======================
            // APPROVAL META
            // ======================
            $table->string('sign_type')->index();               // manager | senior_manager
            $table->unsignedBigInteger('user_id')->index();     // approver

            // ======================
            // AUDIT TRAIL
            // ======================
            $table->unsignedBigInteger('issued_by')->nullable()->index();
            $table->string('issued_channel')->nullable();       // system | wa | manual
            $table->string('ip_issued')->nullable();
            $table->text('ua_issued')->nullable();

            // ======================
            // LIFECYCLE
            // ======================
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('used_at')->nullable()->index();

            // ======================
            // TIMESTAMPS
            // ======================
            $table->timestamps();

            // ======================
            // COMPOSITE INDEX (KRUSIAL)
            // ======================
            $table->index([
                'nomor_spk',
                'sign_type',
                'user_id',
            ], 'spk_token_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk_approval_tokens');
    }
}
