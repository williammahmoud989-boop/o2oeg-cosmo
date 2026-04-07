<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->decimal('average_rating', 3, 2)->default(0)->nullable();
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('attendance_rate', 5, 2)->default(0);
            $table->integer('total_bookings')->default(0);
            $table->integer('completed_bookings')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('total_commission', 12, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('late_days')->default(0);
            $table->timestamps();
            $table->unique(['staff_id', 'month', 'year']);
            $table->index(['staff_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_performance_metrics');
    }
};
