    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('dokumen_orders', function (Blueprint $table) {
    $table->id(); // Primary key unik tiap baris

    $table->string('notification_number');
    $table->foreign('notification_number')
          ->references('notification_number')
          ->on('notifications')
          ->onDelete('cascade');

    $table->enum('jenis_dokumen', ['abnormalitas', 'scope_of_work', 'gambar_teknik']);

    $table->string('file_path')->nullable();
    $table->string('keterangan')->nullable();

    // Scope of Work detail
    $table->string('nama_pekerjaan')->nullable();
    $table->string('unit_kerja')->nullable();
    $table->date('tanggal_pemakaian')->nullable();
    $table->date('tanggal_dokumen')->nullable();
    $table->json('scope_pekerjaan')->nullable();
    $table->json('qty')->nullable();
    $table->json('satuan')->nullable();
    $table->json('keterangan_pekerjaan')->nullable();
    $table->text('catatan')->nullable();
    $table->string('nama_penginput')->nullable();
    $table->text('tanda_tangan')->nullable();

    $table->timestamps();

    // 🔒 Satu notification_number hanya boleh punya 1 row per jenis_dokumen
    $table->unique(['notification_number', 'jenis_dokumen']);
});

        }

        public function down(): void
        {
            Schema::dropIfExists('dokumen_orders');
        }
    };
