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
    Schema::table('transactions', function (Blueprint $table) {
        $table->string('reseller_name')->nullable()->after('api_source_id');
        $table->string('msisdn')->nullable()->after('reseller_name');
        $table->string('supplier')->nullable()->after('msisdn');
        $table->string('product_code')->nullable()->after('supplier');
        $table->string('sn')->nullable()->after('product_code');
        $table->string('request_id')->nullable()->after('sn');
        $table->bigInteger('debit')->default(0)->after('request_id');
        $table->bigInteger('credit')->default(0)->after('debit');
        $table->bigInteger('balance')->default(0)->after('credit');
        $table->bigInteger('profit')->default(0)->after('balance');
    });
}

public function down(): void
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropColumn(['reseller_name','msisdn','supplier','product_code','sn','request_id','debit','credit','balance','profit']);
    });
}

    
};
