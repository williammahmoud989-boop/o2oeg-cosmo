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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn(['discount_amount', 'points_earned', 'points_redeemed']);
        });
    }
};
