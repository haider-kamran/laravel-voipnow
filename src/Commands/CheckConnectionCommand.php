<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow\Commands;

use Illuminate\Console\Command;
use HyderKamran\VoipNow\VoipNowClient;
use HyderKamran\VoipNow\Exception\VoipNowException;

class CheckConnectionCommand extends Command
{
    protected $signature = 'voipnow:check
                            {--adapter= : Override adapter for this check (rest|soap)}';

    protected $description = 'Test the VoipNow API connection and display configuration status.';

    public function handle(VoipNowClient $client): int
    {
        $config = $client->getConfigurations();

        $this->info('VoipNow Connection Check');
        $this->line(str_repeat('─', 50));

        $this->displayConfigStatus($config);

        $this->line('');
        $this->line('Testing connection...');

        try {
            $info = $client->GetSystemInfo();
            $this->info('✓ Connection successful!');

            if (!empty($info)) {
                $this->line('');
                $this->line('<fg=cyan>Server Info:</>');
                foreach ($info as $key => $value) {
                    if (is_scalar($value)) {
                        $this->line("  <fg=gray>{$key}:</> {$value}");
                    }
                }
            }

            return self::SUCCESS;
        } catch (VoipNowException $e) {
            $this->error('✗ Connection failed: ' . $e->getMessage());
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('✗ Unexpected error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function displayConfigStatus(array $config): void
    {
        $checks = [
            'Adapter'    => $config['adapter'] ?? 'rest',
            'Domain'     => $config['voip_domain'] ?? '(not set)',
            'Key'        => empty($config['voip_key']) ? '<fg=red>✗ Not set</>' : '<fg=green>✓ Set</>',
            'Secret'     => empty($config['voip_secret']) ? '<fg=red>✗ Not set</>' : '<fg=green>✓ Set</>',
        ];

        foreach ($checks as $label => $value) {
            $this->line(sprintf('  <fg=cyan>%-12s</> %s', $label . ':', $value));
        }

        if (empty($config['voip_domain'])) {
            $this->warn('  VOIPNOW_DOMAIN is not configured.');
        }
    }
}
