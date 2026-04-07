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
        Schema::create('influencers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('referral_code')->unique();
            $table->decimal('commission_rate', 5, 2)->default(10.00); // Percentage
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('instagram_handle')->nullable();
            $table->text('payment_info')->nullable(); // Vodafone Cash / Bank
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencers');
    }
};
