<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('total_bookings')->default(0);
            $table->integer('completed_bookings')->default(0);
            $table->integer('cancelled_bookings')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('commission_charged', 12, 2)->default(0);
            $table->decimal('net_revenue', 12, 2)->default(0);
            $table->integer('unique_customers')->default(0);
            $table->integer('returning_customers')->default(0);
            $table->decimal('average_booking_value', 10, 2)->default(0);
            $table->decimal('occupancy_rate', 5, 2)->default(0)->comment('0-100%');
            $table->timestamps();
            $table->unique(['salon_id', 'date']);
            $table->index(['salon_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_metrics');
    }
};
