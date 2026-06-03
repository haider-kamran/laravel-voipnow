<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow;

use Illuminate\Contracts\Config\Repository;
use HyderKamran\VoipNow\Contracts\ConnectorInterface;
use HyderKamran\VoipNow\Enums\HttpMethod;
use HyderKamran\VoipNow\Exception\VoipNowException;
use HyderKamran\VoipNow\Traits\ResolvesMagicEndpoints;
use HyderKamran\VoipNow\VoipNowSoapClient;

/**
 * VoipNowClient
 *
 * The main entry-point for interacting with the VoipNow UnifiedAPI v5.
 * Resolves the configured adapter (REST or SOAP), establishes a connection,
 * and exposes typed proxy methods for all HTTP verbs plus a set of
 * first-class helper methods for common VoipNow resources.
 *
 * Usage via Facade:
 *   VoipNow::get('organizations')
 *   VoipNow::post('organizations', [...])
 *   VoipNow::GetServiceProviders()   // backward-compatible helper
 *
 * @method array GetServiceProviders(array $params = [])
 * @method array GetOrganizations(array $params = [])
 * @method array GetUsers(array $params = [])
 * @method array GetExtensions(array $params = [])
 * @method array GetUserGroups(array $params = [])
 * @method array GetChargingPlans(array $params = [])
 */
class VoipNowClient
{
    use ResolvesMagicEndpoints;

    protected Repository $config;
    protected ConnectorInterface $connector;
    protected ?VoipNowSoapClient $soapClient = null;

    /** Lazy-initialised after connect() is called. */
    protected ?ConnectorInterface $connection = null;

    public function __construct(
        Repository $config,
        ConnectorInterface $connector,
        ?VoipNowSoapClient $soapClient = null
    ) {
        $this->config     = $config;
        $this->connector  = $connector;
        $this->soapClient = $soapClient;
    }

    /**
     * Access the VoipNow SystemAPI (SOAP) client.
     *
     * Use this for account provisioning: organizations, extensions,
     * users, billing, charging plans, PBX configuration, and reporting.
     *
     * @see https://wiki.4psa.com/VoipNow/Developer-Guide/SystemAPI/operations.html
     *
     * @example VoipNow::soap()->GetOrganizations()
     * @example VoipNow::soap()->AddExtension(['name' => 'Reception', 'number' => '100'])
     * @example VoipNow::soap()->GetChargingPlans()
     */
    public function soap(): VoipNowSoapClient
    {
        if ($this->soapClient === null) {
            throw new VoipNowException('SystemAPI SOAP client is not available in this context.');
        }

        return $this->soapClient;
    }

    // -----------------------------------------------------------------------
    // Connection
    // -----------------------------------------------------------------------

    protected function getConnection(): ConnectorInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->connector->connect($this->getConfigurations());
        }

        return $this->connection;
    }

    public function getConfigurations(): array
    {
        $cfg = $this->config->get('voipnow', []);

        if (!is_array($cfg)) {
            throw new VoipNowException('VoipNow configuration must be defined as an array.');
        }

        return $cfg;
    }

    // -----------------------------------------------------------------------
    // Core HTTP verbs
    // -----------------------------------------------------------------------

    /**
     * Perform a GET request.
     *
     * @param  string  $endpoint     e.g. 'organizations', 'users', 'extensions'
     * @param  array   $queryParams  Key-value pairs appended as query string
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        return $this->getConnection()->get($endpoint, $queryParams);
    }

    /**
     * Perform a POST request (create a resource).
     *
     * @param  string  $endpoint
     * @param  array   $data   JSON body payload
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->getConnection()->post($endpoint, $data);
    }

    /**
     * Perform a PUT request (replace / full-update a resource).
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->getConnection()->put($endpoint, $data);
    }

    /**
     * Perform a PATCH request (partial update of a resource).
     */
    public function patch(string $endpoint, array $data = []): array
    {
        return $this->getConnection()->patch($endpoint, $data);
    }

    /**
     * Perform a DELETE request.
     */
    public function delete(string $endpoint, array $data = []): array
    {
        return $this->getConnection()->delete($endpoint, $data);
    }

    // -----------------------------------------------------------------------
    // First-class resource helpers (UnifiedAPI v5 endpoints)
    // -----------------------------------------------------------------------

    /** List all service providers. */
    public function GetServiceProviders(array $params = []): array
    {
        return $this->get('service-providers', $params);
    }

    /** List all organizations. */
    public function GetOrganizations(array $params = []): array
    {
        return $this->get('organizations', $params);
    }

    /** Get a single organization by ID. */
    public function GetOrganizationDetails(array $params = []): array
    {
        $id = $params['ID'] ?? $params['identifier'] ?? null;

        if ($id === null) {
            throw new VoipNowException('GetOrganizationDetails requires an "ID" or "identifier" parameter.');
        }

        return $this->get('organizations/' . $id);
    }

    /** List all users. */
    public function GetUsers(array $params = []): array
    {
        return $this->get('users', $params);
    }

    /** List all extensions. */
    public function GetExtensions(array $params = []): array
    {
        return $this->get('extensions', $params);
    }

    /** List all user groups. */
    public function GetUserGroups(array $params = []): array
    {
        return $this->get('user-groups', $params);
    }

    /** List all charging plans. */
    public function GetChargingPlans(array $params = []): array
    {
        return $this->get('charging-plans', $params);
    }

    /** Get system information. */
    public function GetSystemInfo(array $params = []): array
    {
        return $this->get('system-info', $params);
    }

    /** List all phone numbers. */
    public function GetPhoneNumbers(array $params = []): array
    {
        return $this->get('phone-numbers', $params);
    }

    /** List all call queues. */
    public function GetCallQueues(array $params = []): array
    {
        return $this->get('call-queues', $params);
    }

    /** List all IVR menus. */
    public function GetIVRs(array $params = []): array
    {
        return $this->get('ivr', $params);
    }

    /** List all sound files. */
    public function GetSounds(array $params = []): array
    {
        return $this->get('sounds', $params);
    }

    /** List call history / CDRs. */
    public function GetCallHistory(array $params = []): array
    {
        return $this->get('call-history', $params);
    }

    /**
     * Fetch a single resource by ID.
     *
     * @example VoipNow::find('organizations', 42)
     * @example VoipNow::find('extensions', 'ext-101')
     */
    public function find(string $resource, int|string $id): array
    {
        return $this->get($resource . '/' . $id);
    }

    /**
     * Paginate a resource using VoipNow's limit/offset convention.
     *
     * @param  string  $resource   e.g. 'organizations', 'users'
     * @param  int     $page       1-indexed page number
     * @param  int     $perPage    Records per page (default 20)
     * @param  array   $extra      Any additional query parameters
     */
    public function paginate(string $resource, int $page = 1, int $perPage = 20, array $extra = []): array
    {
        $offset = ($page - 1) * $perPage;

        return $this->get($resource, array_merge($extra, [
            'limit'  => $perPage,
            'offset' => $offset,
        ]));
    }

    // -----------------------------------------------------------------------
    // Magic fallback — converts StudlyCase method names to kebab-case endpoints
    // e.g.  VoipNow::GetFaxes() → GET /api/v5/faxes
    // -----------------------------------------------------------------------

    public function __call(string $method, array $parameters): mixed
    {
        // Strip leading verb prefix (Get / Add / Update / Remove)
        $endpoint = preg_replace('/^(Get|Add|Update|Remove)/', '', $method);

        // StudlyCase → kebab-case: "ServiceProviders" → "service-providers"
        $endpoint = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $endpoint));

        $params = $parameters[0] ?? [];
        $verb = $this->resolveHttpMethodFromMethod($method);

        return match ($verb) {
            HttpMethod::POST => $this->post($endpoint, $params),
            HttpMethod::PUT => $this->put($endpoint, $params),
            HttpMethod::DELETE => $this->delete($endpoint, $params),
            default => $this->get($endpoint, $params),
        };
    }
}
