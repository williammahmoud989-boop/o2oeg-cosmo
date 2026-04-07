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
        Schema::table('staff', function (Blueprint $table) {
            $table->boolean('attendance_reminder_enabled')->default(false)->after('commission_rate');
            $table->time('attendance_time')->nullable()->after('attendance_reminder_enabled');
            $table->string('whatsapp_number')->nullable()->after('attendance_time');
            $table->boolean('privacy_consent')->default(false)->after('whatsapp_number');
            $table->timestamp('consent_given_at')->nullable()->after('privacy_consent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['attendance_reminder_enabled', 'attendance_time', 'whatsapp_number', 'privacy_consent', 'consent_given_at']);
        });
    }
};