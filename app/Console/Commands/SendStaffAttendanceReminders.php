<?php

namespace App\Console\Commands;

use App\Services\StaffAttendanceReminderService;
use Illuminate\Console\Command;

class SendStaffAttendanceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staff:send-attendance-reminders {--dry-run : Run without actually sending messages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send attendance reminder messages to staff members at their scheduled times';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = app(StaffAttendanceReminderService::class);

        if ($this->option('dry-run')) {
            $this->info('Dry run mode - no messages will be sent');
            // In dry run, just log what would be done
            $staffMembers = \App\Models\Staff::where('attendance_reminder_enabled', true)
                ->where('privacy_consent', true)
                ->whereNotNull('whatsapp_number')
                ->whereNotNull('attendance_time')
                ->get();

            foreach ($staffMembers as $staff) {
                if (now()->format('H:i') === $staff->attendance_time->format('H:i')) {
                    $this->info("Would send message to: {$staff->name} at {$staff->whatsapp_number}");
                }
            }
        } else {
            $service->scheduleReminders();
            $this->info('Attendance reminders sent successfully');
        }

        return Command::SUCCESS;
    }
}