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
        Schema::create('user_package_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            
            // Optional: The booking ID during which this package session was redeemed
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_package_usages');
    }
};
