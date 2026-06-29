<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel master supplier
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();           // Kode unik supplier (ex: SAMANTARA, TELKOM)
            $table->string('nama');                     // Nama supplier
            $table->string('url')->nullable();          // Base URL API supplier
            $table->string('method')->default('GET');   // HTTP method: GET, POST, dll
            $table->json('headers')->nullable();        // Custom headers (JSON)
            $table->json('body')->nullable();           // Request body (JSON, untuk POST)
            $table->string('status')->default('active');// active / inactive
            $table->text('keterangan')->nullable();     // Catatan tambahan
            $table->timestamps();
        });

        // Tabel hasil rekon (log sync per supplier)
        Schema::create('supplier_rekon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->date('tanggal');                    // Tanggal rekon
            $table->bigInteger('saldo')->default(0);    // Saldo supplier hasil sync
            $table->integer('total_trx')->default(0);   // Jumlah transaksi di supplier
            $table->bigInteger('total_amount')->default(0); // Total amount di supplier
            $table->string('rc')->nullable();           // Response code dari API
            $table->text('response_raw')->nullable();   // Raw response (untuk debug)
            $table->string('status')->default('sukses');// sukses / gagal
            $table->text('keterangan')->nullable();     // Pesan error jika gagal
            $table->timestamps();

            $table->index(['supplier_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_rekon');
        Schema::dropIfExists('suppliers');
    }
};