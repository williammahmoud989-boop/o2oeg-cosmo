# Staff Attendance Reminder Scheduler
# PowerShell script for Windows scheduling

param(
    [switch]$DryRun
)

$projectPath = "C:\Users\Goodm\.gemini\antigravity\scratch\O2OEGCosmo\backend"
$logFile = Join-Path $projectPath "attendance_log.txt"

# Change to project directory
Set-Location $projectPath

# Run the command
if ($DryRun) {
    $command = "php artisan staff:send-attendance-reminders --dry-run"
} else {
    $command = "php artisan staff:send-attendance-reminders"
}

try {
    $output = Invoke-Expression $command
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "$timestamp - Command executed successfully`nOutput: $output`n---"
    Add-Content -Path $logFile -Value $logEntry
    Write-Host "Attendance reminders processed successfully"
} catch {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $errorMessage = "$timestamp - Error: $($_.Exception.Message)"
    Add-Content -Path $logFile -Value $errorMessage
    Write-Host "Error occurred: $($_.Exception.Message)"
}