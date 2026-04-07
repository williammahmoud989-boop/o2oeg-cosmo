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
        Schema::create('reviews', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('salon_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('booking_id')->constrained()->onDelete('cascade');
            $blueprint->integer('rating')->default(5);
            $blueprint->text('comment')->nullable();
            $blueprint->text('reply')->nullable();
            $blueprint->timestamp('replied_at')->nullable();
            $blueprint->boolean('is_public')->default(true);
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
