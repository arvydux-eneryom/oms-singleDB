<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client;

class UpdateTwilioWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twilio:update-webhook {ngrokUrl?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Twilio phone number webhook URL (auto-detects ngrok URL if running)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.token');
        $phoneNumber = config('services.twilio.from');

        // Get ngrok URL - either from argument or auto-detect
        $ngrokUrl = $this->argument('ngrokUrl');

        if (! $ngrokUrl) {
            $this->info('No URL provided. Attempting to auto-detect ngrok URL...');
            $ngrokUrl = $this->getNgrokUrl();

            if (! $ngrokUrl) {
                $this->error('✗ Could not detect ngrok URL. Make sure ngrok is running.');
                $this->newLine();
                $this->line('You can manually specify the URL:');
                $this->line('  php artisan twilio:update-webhook https://xxxx.ngrok-free.app');

                return 1;
            }

            $this->info("✓ Detected ngrok URL: {$ngrokUrl}");
            $this->newLine();
        }

        $webhookUrl = rtrim($ngrokUrl, '/').'/twilio/sms/incoming';

        try {
            $client = new Client($accountSid, $authToken);

            // Get all phone numbers
            $incomingPhoneNumbers = $client->incomingPhoneNumbers->read();

            // Find the matching phone number
            $found = false;
            foreach ($incomingPhoneNumbers as $number) {
                if ($number->phoneNumber === $phoneNumber) {
                    $found = true;

                    // Show current configuration
                    $currentWebhook = $number->smsUrl ?: '(not set)';

                    $this->line("Phone Number: {$phoneNumber}");
                    $this->line("Phone Number SID: {$number->sid}");
                    $this->newLine();
                    $this->line("Current Twilio webhook: {$currentWebhook}");
                    $this->line("Detected ngrok webhook: {$webhookUrl}");
                    $this->newLine();

                    // Check if update is needed
                    $webhookNeedsUpdate = $currentWebhook !== $webhookUrl;

                    if (! $webhookNeedsUpdate) {
                        $this->info('✓ Twilio webhook is already up to date.');
                        $this->newLine();

                        // Still check if local config files need updating
                        $configUpdated = false;

                        if ($this->envNeedsUpdate($ngrokUrl)) {
                            $this->updateEnvFile($ngrokUrl);
                            $configUpdated = true;
                        }

                        if ($this->tenancyConfigNeedsUpdate($ngrokUrl)) {
                            $this->updateTenancyConfig($ngrokUrl);
                            $configUpdated = true;
                        }

                        if ($configUpdated) {
                            $this->call('config:clear');
                            $this->call('route:clear');
                            $this->newLine();
                            $this->info('✓ Configuration files updated and caches cleared.');
                        } else {
                            $this->line('✓ All configuration files are up to date.');
                        }

                        return 0;
                    }

                    // Confirm update
                    if (! $this->confirm('Do you want to update the Twilio webhook?', true)) {
                        $this->line('Update cancelled.');

                        return 0;
                    }

                    // Update the webhook
                    $client->incomingPhoneNumbers
                        ->get($number->sid) // @phpstan-ignore method.notFound
                        ->update([
                            'smsUrl' => $webhookUrl,
                            'smsMethod' => 'POST',
                        ]);

                    $this->newLine();
                    $this->info('✓ Twilio webhook updated successfully!');

                    // Update .env and config files
                    $this->updateEnvFile($ngrokUrl);
                    $this->updateTenancyConfig($ngrokUrl);

                    // Clear config cache to ensure changes take effect
                    $this->call('config:clear');
                    $this->call('route:clear');

                    $this->newLine();
                    $this->info('✓ Configuration files updated and caches cleared.');

                    break;
                }
            }

            if (! $found) {
                $this->error("✗ Phone number {$phoneNumber} not found in your Twilio account.");
                $this->newLine();
                $this->line('Available phone numbers:');
                foreach ($incomingPhoneNumbers as $number) {
                    $this->line("  - {$number->phoneNumber}");
                }

                return 1;
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('✗ Error: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Get the current ngrok public URL from the ngrok API.
     */
    protected function getNgrokUrl(): ?string
    {
        try {
            // Query ngrok API (default runs on localhost:4040)
            $response = Http::timeout(3)->get('http://localhost:4040/api/tunnels');

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            // Find the HTTPS tunnel
            if (isset($data['tunnels']) && is_array($data['tunnels'])) {
                foreach ($data['tunnels'] as $tunnel) {
                    if (isset($tunnel['public_url']) && str_starts_with($tunnel['public_url'], 'https://')) {
                        return $tunnel['public_url'];
                    }
                }
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if .env file needs updating.
     */
    protected function envNeedsUpdate(string $ngrokUrl): bool
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);

        // Check if TWILIO_SMS_COMMON_URL matches the current ngrok URL
        if (preg_match('/^TWILIO_SMS_COMMON_URL=(.*)$/m', $envContent, $matches)) {
            $currentUrl = trim($matches[1]);

            return $currentUrl !== $ngrokUrl;
        }

        return false;
    }

    /**
     * Update the .env file with the new ngrok URL.
     */
    protected function updateEnvFile(string $ngrokUrl): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->warn('⚠ .env file not found. Skipping .env update.');

            return;
        }

        $envContent = file_get_contents($envPath);

        // Update TWILIO_SMS_COMMON_URL
        $pattern = '/^TWILIO_SMS_COMMON_URL=.*/m';
        $replacement = "TWILIO_SMS_COMMON_URL={$ngrokUrl}";

        if (preg_match($pattern, $envContent)) {
            $newContent = preg_replace($pattern, $replacement, $envContent);
            file_put_contents($envPath, $newContent);
            $this->line('  → Updated TWILIO_SMS_COMMON_URL in .env');
        } else {
            $this->warn('⚠ TWILIO_SMS_COMMON_URL not found in .env');
        }
    }

    /**
     * Check if config/tenancy.php needs updating.
     */
    protected function tenancyConfigNeedsUpdate(string $ngrokUrl): bool
    {
        $tenancyPath = config_path('tenancy.php');

        if (! file_exists($tenancyPath)) {
            return false;
        }

        $tenancyContent = file_get_contents($tenancyPath);

        // Extract domain from ngrok URL
        $ngrokDomain = str_replace(['https://', 'http://'], '', $ngrokUrl);
        $ngrokDomain = rtrim($ngrokDomain, '/');

        // Check if this domain is already in the config
        return strpos($tenancyContent, $ngrokDomain) === false;
    }

    /**
     * Update the config/tenancy.php file with the new ngrok domain.
     */
    protected function updateTenancyConfig(string $ngrokUrl): void
    {
        $tenancyPath = config_path('tenancy.php');

        if (! file_exists($tenancyPath)) {
            $this->warn('⚠ config/tenancy.php not found. Skipping tenancy config update.');

            return;
        }

        $tenancyContent = file_get_contents($tenancyPath);

        // Extract domain from ngrok URL (remove https://)
        $ngrokDomain = str_replace(['https://', 'http://'], '', $ngrokUrl);
        $ngrokDomain = rtrim($ngrokDomain, '/');

        // Update the ngrok domain in central_domains array
        // This regex matches the line with ngrok-free.app and replaces it
        $pattern = "/('|\")([a-z0-9]+\.ngrok-free\.app)('|\")/";
        $replacement = "'{$ngrokDomain}'";

        if (preg_match($pattern, $tenancyContent)) {
            $newContent = preg_replace($pattern, $replacement, $tenancyContent);
            file_put_contents($tenancyPath, $newContent);
            $this->line('  → Updated central_domains in config/tenancy.php');
        } else {
            $this->warn('⚠ ngrok domain not found in config/tenancy.php central_domains');
        }
    }
}
