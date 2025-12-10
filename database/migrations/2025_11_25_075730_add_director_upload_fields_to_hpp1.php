<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDirectorUploadFieldsToHpp1 extends Migration
{
    public function up()
    {
        Schema::table('hpp1', function (Blueprint $table) {
            $table->string('director_uploaded_file')->nullable()->after('director_signature_user_id');
            $table->timestamp('director_uploaded_at')->nullable()->after('director_uploaded_file');
            $table->string('director_uploaded_by')->nullable()->after('director_uploaded_at');
            $table->index('director_uploaded_at');
        });
    }

    public function down()
    {
        Schema::table('hpp1', function (Blueprint $table) {
            if (Schema::hasColumn('hpp1', 'director_uploaded_file')) {
                $table->dropColumn('director_uploaded_file');
            }
            if (Schema::hasColumn('hpp1', 'director_uploaded_at')) {
                $table->dropColumn('director_uploaded_at');
            }
            if (Schema::hasColumn('hpp1', 'director_uploaded_by')) {
                $table->dropColumn('director_uploaded_by');
            }
        });
    }
}
