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
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('api_source_id')->nullable();
        $table->string('trx_id')->unique();
        $table->dateTime('trx_date');
        $table->decimal('amount', 15, 2);
        $table->string('status');
        $table->string('customer_name')->nullable();
        $table->timestamps();

        $table->foreign('api_source_id')->references('id')->on('api_sources')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
