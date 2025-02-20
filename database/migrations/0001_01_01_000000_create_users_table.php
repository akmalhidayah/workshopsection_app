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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('initials')->nullable(); // Menambahkan kolom inisial
            $table->string('email')->unique();
            $table->string('usertype')->default('user');
            $table->string('departemen')->nullable(); 
            $table->string('unit_work')->nullable();  
            $table->string('seksi')->nullable();      
            $table->string('jabatan')->nullable();    
            $table->string('whatsapp_number')->nullable(); 
            $table->json('related_units')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('initials'); // Menghapus kolom initials
            $table->dropColumn('departemen');
            $table->dropColumn('unit_work');
            $table->dropColumn('seksi');
            $table->dropColumn('jabatan');
            $table->dropColumn('whatsapp_number'); 
            $table->dropColumn('related_units');   
        });

        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
