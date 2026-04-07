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
        Schema::table('salons', function (Blueprint $table) {
            $table->text('paymob_api_key')->nullable()->after('payment_methods');
            $table->text('paymob_hmac_secret')->nullable()->after('paymob_api_key');
            $table->string('paymob_card_integration_id')->nullable()->after('paymob_hmac_secret');
            $table->string('paymob_iframe_id')->nullable()->after('paymob_card_integration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn([
                'paymob_api_key',
                'paymob_hmac_secret',
                'paymob_card_integration_id',
                'paymob_iframe_id'
            ]);
        });
    }
};
