<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow\Traits;

use HyderKamran\VoipNow\Enums\AdapterType;
use HyderKamran\VoipNow\Exception\VoipNowException;

trait HandlesVoipNowConfig
{
    protected function normalizeConfig(array $config): array
    {
        return array_merge([
            'adapter' => AdapterType::REST->value,
            'voip_version' => '',
            'voip_domain' => '',
            'voip_key' => '',
            'voip_secret' => '',
            'voip_parent_identifier' => '',
            'wsdl_url' => '',
            'soap_options' => [],
        ], $config);
    }

    protected function getDomain(array $config): string
    {
        return rtrim((string) ($config['voip_domain'] ?? ''), '/');
    }

    protected function getTokenEndpoint(array $config): string
    {
        $domain = $this->getDomain($config);

        if ($domain === '') {
            throw new VoipNowException('The VOIPNOW_DOMAIN configuration value is required.');
        }

        return $domain . '/oauth/token.php';
    }

    protected function requireCredentials(array $config): void
    {
        if (empty($config['voip_key']) || empty($config['voip_secret'])) {
            throw new VoipNowException('VOIPNOW_KEY and VOIPNOW_SECRET must be configured.');
        }
    }
}
