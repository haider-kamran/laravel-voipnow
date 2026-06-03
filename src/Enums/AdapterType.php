<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow\Enums;

enum AdapterType: string
{
    case REST = 'rest';
    case SOAP = 'soap';

    public static function fromString(string $value): self
    {
        return match (strtolower(trim($value))) {
            self::SOAP->value => self::SOAP,
            default => self::REST,
        };
    }

    public function isSoap(): bool
    {
        return $this === self::SOAP;
    }

    public function isRest(): bool
    {
        return $this === self::REST;
    }
}
