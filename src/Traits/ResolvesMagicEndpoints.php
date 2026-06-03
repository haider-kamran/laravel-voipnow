<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow\Traits;

use HyderKamran\VoipNow\Enums\HttpMethod;

trait ResolvesMagicEndpoints
{
    protected function resolveEndpointFromMethod(string $method): string
    {
        $endpoint = preg_replace('/^(Get|Add|Update|Remove)/', '', $method);
        $endpoint = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $endpoint));

        return $endpoint;
    }

    protected function resolveHttpMethodFromMethod(string $method): HttpMethod
    {
        return match (true) {
            str_starts_with($method, 'Add') => HttpMethod::POST,
            str_starts_with($method, 'Update') => HttpMethod::PUT,
            str_starts_with($method, 'Remove') => HttpMethod::DELETE,
            default => HttpMethod::GET,
        };
    }
}
