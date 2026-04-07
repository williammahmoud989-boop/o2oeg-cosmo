@echo off
REM Staff Attendance Reminder Scheduler
REM This script runs the Laravel command every minute for testing
REM In production, use Windows Task Scheduler or cron on Linux

cd /d "C:\Users\Goodm\.gemini\antigravity\scratch\O2OEGCosmo\backend"
php artisan staff:send-attendance-reminders

REM Log the execution
echo %date% %time% - Attendance reminders sent >> attendance_log.txt