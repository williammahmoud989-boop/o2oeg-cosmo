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
            if (!Schema::hasColumn('salons', 'vodafone_cash_number')) {
                $table->string('vodafone_cash_number')->nullable()->after('deposit_percentage');
            }
            if (!Schema::hasColumn('salons', 'instapay_id')) {
                $table->string('instapay_id')->nullable()->after('vodafone_cash_number');
            }
            if (!Schema::hasColumn('salons', 'deposit_days')) {
                $table->json('deposit_days')->nullable()->after('instapay_id');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'payment_receipt')) {
                $table->string('payment_receipt')->nullable()->after('deposit_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn(['vodafone_cash_number', 'instapay_id', 'deposit_days']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_receipt']);
        });
    }
};
