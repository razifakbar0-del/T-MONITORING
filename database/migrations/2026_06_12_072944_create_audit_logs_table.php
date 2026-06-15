<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Siapa yang melakukan
        $table->string('activity');   // Jenis aktivitas (e.g., 'CREATE', 'DELETE')
        $table->string('model_type'); // Model yang diakses (e.g., 'Transaction', 'User')
        $table->unsignedBigInteger('model_id')->nullable(); // ID data yang diubah
        $table->text('details')->nullable(); // Catatan tambahan atau data lama/baru
        $table->timestamps();
    });
}
};
