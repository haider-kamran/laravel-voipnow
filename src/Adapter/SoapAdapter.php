<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow\Adapter;

use HyderKamran\VoipNow\Contracts\ConnectorInterface;
use HyderKamran\VoipNow\Exception\VoipNowException;
use HyderKamran\VoipNow\Traits\HandlesVoipNowConfig;

/**
 * SoapAdapter — legacy adapter for the VoipNow SystemAPI (SOAP/WSDL).
 *
 * Set VOIPNOW_ADAPTER=soap in your .env to activate this adapter.
 * The WSDL schema auto-generates method stubs such as GetServiceProviders(),
 * so calls are forwarded via PHP's native SoapClient magic methods.
 *
 * Note: SOAP support requires the php-soap extension.
 */
class SoapAdapter implements ConnectorInterface
{
    use HandlesVoipNowConfig;

    protected array $config = [];

    /** @var \SoapClient|null */
    protected $soapClient = null;

    public function connect(array $config): static
    {
        if (!extension_loaded('soap')) {
            throw new VoipNowException('The php-soap extension is required to use the SoapAdapter.');
        }

        $this->config = $this->normalizeConfig($config);
        $this->soapClient = $this->createSoapClient();

        return $this;
    }

    protected function createSoapClient(): \SoapClient
    {
        $domain = $this->getDomain($this->config);

        if (empty($domain)) {
            throw new VoipNowException('The VOIPNOW_DOMAIN configuration value is required.');
        }

        $wsdlUrl = $this->config['wsdl_url'] ?: $domain . '/soap2/schema/latest/voipnowservice.wsdl';

        $defaultOptions = [
            'uri'                => 'http://schemas.xmlsoap.org/soap/envelope/',
            'style'              => SOAP_RPC,
            'use'                => SOAP_ENCODED,
            'soap_version'       => SOAP_1_1,
            'cache_wsdl'         => WSDL_CACHE_BOTH,
            'connection_timeout' => 60,
            'trace'              => true,
            'encoding'           => 'UTF-8',
            'exceptions'         => true,
        ];

        $options = array_merge($defaultOptions, $this->config['soap_options'] ?? []);

        $client = new \SoapClient($wsdlUrl, $options);

        $version = $this->config['voip_version'] ?? '';
        $auth = new \stdClass();
        $auth->accessToken = $this->getToken();
        $authValues = new \SoapVar(
            $auth,
            SOAP_ENC_OBJECT,
            'http://4psa.com/HeaderData.xsd/' . $version
        );

        $header = new \SoapHeader(
            'http://4psa.com/HeaderData.xsd/' . $version,
            'userCredentials',
            $authValues,
            false
        );

        $client->__setSoapHeaders([$header]);

        return $client;
    }

    protected function getToken(): string
    {
        $this->requireCredentials($this->config);

        $response = (new \GuzzleHttp\Client())->post($this->getTokenEndpoint($this->config), [
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->config['voip_key'],
                'client_secret' => $this->config['voip_secret'],
            ],
            'http_errors' => false,
        ]);

        $payload = json_decode((string) $response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($payload['access_token'])) {
            throw new VoipNowException('Could not retrieve VoipNow access token via SOAP flow.');
        }

        return $payload['access_token'];
    }

    /**
     * Forward arbitrary SOAP method calls (e.g. GetOrganizations, GetUsers).
     *
     * @throws VoipNowException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if ($this->soapClient === null) {
            throw new VoipNowException('SoapAdapter is not connected. Call connect() first.');
        }

        try {
            return $this->soapClient->{$name}(...$arguments);
        } catch (\SoapFault $e) {
            throw new VoipNowException('SOAP call [' . $name . '] failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    // -----------------------------------------------------------------------
    // REST-like shim methods so SoapAdapter satisfies ConnectorInterface.
    // These translate REST-style calls to SOAP equivalents where possible.
    // -----------------------------------------------------------------------

    public function get(string $endpoint, array $queryParams = []): array
    {
        $method = $this->endpointToSoapMethod('Get', $endpoint);
        $result = $this->__call($method, [$queryParams]);
        return (array) reset($result);
    }

    public function post(string $endpoint, array $data = []): array
    {
        $method = $this->endpointToSoapMethod('Add', $endpoint);
        $result = $this->__call($method, [$data]);
        return (array) $result;
    }

    public function put(string $endpoint, array $data = []): array
    {
        $method = $this->endpointToSoapMethod('Update', $endpoint);
        $result = $this->__call($method, [$data]);
        return (array) $result;
    }

    public function patch(string $endpoint, array $data = []): array
    {
        return $this->put($endpoint, $data);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        $method = $this->endpointToSoapMethod('Remove', $endpoint);
        $result = $this->__call($method, [$data]);
        return (array) $result;
    }

    /**
     * Convert a REST endpoint slug to a SOAP method name.
     * e.g. 'service-providers' → 'GetServiceProviders'
     */
    protected function endpointToSoapMethod(string $prefix, string $endpoint): string
    {
        $parts = array_map(
            fn(string $part) => ucfirst($part),
            explode('-', $endpoint)
        );

        return $prefix . implode('', $parts);
    }
}
