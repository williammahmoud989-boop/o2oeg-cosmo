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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            
            $table->string('month'); // e.g., '01'
            $table->string('year'); // e.g., '2026'

            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('total_commission', 10, 2)->default(0);
            $table->decimal('advances', 10, 2)->default(0)->comment('السلف');
            $table->decimal('deductions', 10, 2)->default(0)->comment('الخصومات');
            $table->decimal('net_salary', 10, 2)->default(0)->comment('الصافي المستحق');
            
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('payment_date')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Allow only one payroll per staff per month-year combo
            $table->unique(['staff_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
