<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->integer('rating')->min(1)->max(5)->default(5);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['staff_id', 'created_at']);
            $table->unique(['booking_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_reviews');
    }
};
