<?php

namespace HyderKamran\VoipNow;

use Illuminate\Config\Repository;
use HyderKamran\VoipNow\Interface\ConnectorInterface;

class VoipNowClass
{
    protected $wsdlClient;
    protected $config;

    protected $collectionResults = [
        'GetServiceProviders',
        'GetOrganizations',
        'GetUsers',
        'GetExtensions',
        'GetUserGroups',
        'GetChargingPlans',
    ];
    public function __construct(Repository $config, ConnectorInterface $wsdlClient)
    {
        $this->config = $config;

        $this->wsdlClient = $wsdlClient;
    }
    public function connection()
    {
        return $this->wsdlClient->connect($this->getConfigurations());
    }

    public function handle(string $action, $parameters = [])
    {
        $wsdlClient = $this->connection();

        $handleRequest = $wsdlClient->{$action}($parameters);

        if(in_array($action, $this->collectionResults)) {
            return collect(reset($handleRequest));
        }

        return $handleRequest;
    }

    public function __call($method, $parameters)
    {
        return $this->handle($method, ...$parameters);
    }

    public function getConfigurations()
    {
        return $this->config->get('voipnow');
    }

}
