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
            $table->integer('daily_capacity')->default(20)->after('status');
            $table->integer('occupancy_threshold_high')->default(70)->after('daily_capacity');
            $table->decimal('peak_surcharge_percentage', 5, 2)->default(10.00)->after('occupancy_threshold_high');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn(['daily_capacity', 'occupancy_threshold_high', 'peak_surcharge_percentage']);
        });
    }
};
