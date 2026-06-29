<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_mutations', function (Blueprint $table) {
            $table->id();
            $table->string('no_reference')->unique();   // No referensi dari API (unik, cegah duplikat)
            $table->date('tanggal');                    // Tanggal transaksi
            $table->text('keterangan')->nullable();     // Keterangan/deskripsi
            $table->decimal('debet_kredit', 20, 2);    // Positif = kredit, negatif = debet
            $table->decimal('saldo', 20, 2);            // Saldo setelah transaksi
            $table->enum('type', ['kredit', 'debet']); // Jenis mutasi
            $table->string('no_urut')->nullable();      // No urut dari API
            $table->date('start_date')->nullable();     // Periode fetch: mulai
            $table->date('end_date')->nullable();       // Periode fetch: selesai
            $table->timestamps();

            $table->index('tanggal');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_mutations');
    }
};
