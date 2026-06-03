<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow;

use Illuminate\Support\Facades\Facade;

/**
 * VoipNow Facade — UnifiedAPI v5 (REST)
 *
 * For telephony operations: phone calls, events, presence, CDRs, faxes.
 * @see \HyderKamran\VoipNow\VoipNowClient
 *
 * ─── Core HTTP Methods ────────────────────────────────────────────────────
 * @method static array get(string $endpoint, array $queryParams = [])
 * @method static array post(string $endpoint, array $data = [])
 * @method static array put(string $endpoint, array $data = [])
 * @method static array patch(string $endpoint, array $data = [])
 * @method static array delete(string $endpoint, array $data = [])
 *
 * ─── Resource Helpers ────────────────────────────────────────────────────
 * @method static array GetServiceProviders(array $params = [])
 * @method static array GetOrganizations(array $params = [])
 * @method static array GetOrganizationDetails(array $params = [])
 * @method static array GetUsers(array $params = [])
 * @method static array GetExtensions(array $params = [])
 * @method static array GetUserGroups(array $params = [])
 * @method static array GetChargingPlans(array $params = [])
 * @method static array GetSystemInfo(array $params = [])
 * @method static array GetPhoneNumbers(array $params = [])
 * @method static array GetCallQueues(array $params = [])
 * @method static array GetIVRs(array $params = [])
 * @method static array GetSounds(array $params = [])
 * @method static array GetCallHistory(array $params = [])
 *
 * ─── Convenience Helpers ─────────────────────────────────────────────────
 * @method static array find(string $resource, int|string $id)
 * @method static array paginate(string $resource, int $page = 1, int $perPage = 20, array $extra = [])
 *
 * ─── SystemAPI (SOAP) Access ─────────────────────────────────────────────
 * @method static \HyderKamran\VoipNow\VoipNowSoapClient soap()
 */
class VoipNowFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'voipnow';
    }
}
