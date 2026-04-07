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
            $table->string('slug')->unique()->after('name')->nullable(); // nullable temporarily to avoid dropping table
            $table->string('website')->nullable()->after('whatsapp_number');
            $table->string('address_ar')->nullable()->after('address');
            $table->json('payment_methods')->nullable()->after('cover_image');
            $table->decimal('commission_rate', 5, 2)->default(10.00)->after('deposit_percentage')->comment('Platform commission percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn(['slug', 'website', 'address_ar', 'payment_methods', 'commission_rate']);
        });
    }
};
