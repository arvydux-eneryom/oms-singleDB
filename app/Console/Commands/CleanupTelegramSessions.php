<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupTelegramSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up locked or stuck Telegram sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessionDir = storage_path('app/telegram/sessions');

        if (! is_dir($sessionDir)) {
            $this->info('No session directory found. Nothing to clean up.');

            return 0;
        }

        if (! $this->option('force')) {
            if (! $this->confirm('This will remove all lock files and allow new logins. Continue?')) {
                $this->info('Cleanup cancelled.');

                return 0;
            }
        }

        $lockFiles = File::glob($sessionDir.'/**/*.lock');
        $locksRemoved = 0;

        foreach ($lockFiles as $lockFile) {
            if (File::delete($lockFile)) {
                $locksRemoved++;
                $this->line('Removed: '.basename($lockFile));
            }
        }

        $this->info("✓ Removed {$locksRemoved} lock file(s).");
        $this->info('Users can now log in again using QR code or phone number.');

        return 0;
    }
}
