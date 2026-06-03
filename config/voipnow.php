<?php

declare(strict_types=1);

use HyderKamran\VoipNow\Enums\AdapterType;

return [
    'adapter'                => env('VOIPNOW_ADAPTER', AdapterType::REST->value),
    'voip_domain'            => env('VOIPNOW_DOMAIN', ''),
    'voip_key'               => env('VOIPNOW_KEY', ''),
    'voip_secret'            => env('VOIPNOW_SECRET', ''),
    'voip_version'           => env('VOIPNOW_VERSION', ''),
    'voip_parent_identifier' => env('VOIPNOW_PARENT_IDENTIFIER', ''),
    'wsdl_url'               => env('VOIPNOW_WSDL_URL', ''),
    'soap_options'           => [],
];
