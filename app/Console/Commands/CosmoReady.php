<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CosmoReady extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cosmo:ready';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if the O2OEG Cosmo platform is ready for production deployment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->components->info('🚀 Starting O2OEG Cosmo Production Readiness Check...');

        // 1. Database Check
        try {
            DB::connection()->getPdo();
            $this->components->twoColumnDetail('Database Connection', '<fg=green;options=bold>CONNECTED</>');
        } catch (\Exception $e) {
            $this->components->twoColumnDetail('Database Connection', '<fg=red;options=bold>FAILED</>');
            $this->error($e->getMessage());
        }

        // 2. Env Variables Check
        $requiredKeys = [
            'APP_URL',
            'WHATSAPP_PROVIDER',
            'REFERRAL_POINTS',
            'LOYALTY_POINT_VALUE',
        ];

        $missingKeys = [];
        foreach ($requiredKeys as $key) {
            if (empty(env($key))) {
                $missingKeys[] = $key;
            }
        }

        if (empty($missingKeys)) {
            $this->components->twoColumnDetail('Environment Variables', '<fg=green;options=bold>COMPLETE</>');
        } else {
            $this->components->twoColumnDetail('Environment Variables', '<fg=yellow;options=bold>MISSING KEYS</>');
            $this->warn('Missing: ' . implode(', ', $missingKeys));
        }

        // 3. Writable Directories
        $directories = [
            storage_path('logs'),
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            base_path('bootstrap/cache'),
        ];

        $allWritable = true;
        foreach ($directories as $dir) {
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            if (!File::isWritable($dir)) {
                $allWritable = false;
                $this->components->twoColumnDetail('Permissions: ' . basename($dir), '<fg=red;options=bold>NOT WRITABLE</>');
            }
        }

        if ($allWritable) {
            $this->components->twoColumnDetail('Folder Permissions', '<fg=green;options=bold>OK</>');
        }

        // 4. Queue Driver
        $queueDriver = config('queue.default');
        if ($queueDriver === 'sync' || empty($queueDriver)) {
            $this->components->twoColumnDetail('Queue Driver', '<fg=yellow;options=bold>SYNC (Not recommended for bulk)</>');
        } else {
            $this->components->twoColumnDetail('Queue Driver', '<fg=green;options=bold>' . strtoupper($queueDriver) . '</>');
        }

        $this->newLine();
        if ($allWritable && empty($missingKeys)) {
            $this->info('✨ Cosmo is READY for orbit! 🌕');
        } else {
            $this->error('⚠️ Please fix the issues above before launching.');
        }
    }
}
