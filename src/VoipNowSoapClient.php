<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow;

use HyderKamran\VoipNow\Adapter\SoapAdapter;
use HyderKamran\VoipNow\Exception\VoipNowException;
use Illuminate\Contracts\Config\Repository;

/**
 * VoipNowSoapClient
 *
 * Wraps the VoipNow SystemAPI (SOAP/WSDL). Use this client for account
 * provisioning operations — managing organizations, extensions, users,
 * billing, charging plans, PBX configuration, and reporting.
 *
 * For telephony operations (calls, events, presence, CDRs) use
 * VoipNowClient (REST/UnifiedAPI v5) instead.
 *
 * All method names match the official SystemAPI operation names exactly.
 * @see https://wiki.4psa.com/VoipNow/Developer-Guide/SystemAPI/operations.html
 *
 * ─── Account (6 operations) ──────────────────────────────────────────────
 * @method mixed GetSPBrandingColors(array $params = [])
 * @method mixed SetSPBrandingColors(array $params = [])
 * @method mixed GetAccountBillingPackage(array $params = [])
 * @method mixed SetAccountBillingPackage(array $params = [])
 * @method mixed GetAccountPermissions(array $params = [])
 * @method mixed SetAccountPermissions(array $params = [])
 *
 * ─── Billing (32 operations) ─────────────────────────────────────────────
 * @method mixed GetChargingPlans(array $params = [])
 * @method mixed AddChargingPlan(array $params = [])
 * @method mixed UpdateChargingPlan(array $params = [])
 * @method mixed RemoveChargingPlan(array $params = [])
 * @method mixed GetChargingPlanDetails(array $params = [])
 * @method mixed GetCallingCards(array $params = [])
 * @method mixed AddCallingCard(array $params = [])
 * @method mixed UpdateCallingCard(array $params = [])
 * @method mixed RemoveCallingCard(array $params = [])
 * @method mixed GetCallingCardDetails(array $params = [])
 * @method mixed GetCostCenters(array $params = [])
 * @method mixed AddCostCenter(array $params = [])
 * @method mixed RemoveCostCenter(array $params = [])
 * @method mixed GetCostCenterDetails(array $params = [])
 * @method mixed GetBillingStatus(array $params = [])
 * @method mixed SetBillingStatus(array $params = [])
 *
 * ─── Channel (48 operations) ─────────────────────────────────────────────
 * @method mixed GetChannels(array $params = [])
 * @method mixed AddChannel(array $params = [])
 * @method mixed UpdateChannel(array $params = [])
 * @method mixed RemoveChannel(array $params = [])
 * @method mixed GetChannelDetails(array $params = [])
 * @method mixed GetChannelCallCosts(array $params = [])
 * @method mixed SetChannelCallCosts(array $params = [])
 * @method mixed GetChannelGroups(array $params = [])
 * @method mixed AddChannelGroup(array $params = [])
 * @method mixed RemoveChannelGroup(array $params = [])
 *
 * ─── Extension (100 operations) ──────────────────────────────────────────
 * @method mixed GetExtensions(array $params = [])
 * @method mixed AddExtension(array $params = [])
 * @method mixed UpdateExtension(array $params = [])
 * @method mixed RemoveExtension(array $params = [])
 * @method mixed GetExtensionDetails(array $params = [])
 * @method mixed GetExtensionPublicDetails(array $params = [])
 * @method mixed GetExtensionCallCosts(array $params = [])
 * @method mixed SetExtensionCallCosts(array $params = [])
 * @method mixed GetExtensionFeatures(array $params = [])
 * @method mixed SetExtensionFeatures(array $params = [])
 * @method mixed GetExtensionVoicemail(array $params = [])
 * @method mixed SetExtensionVoicemail(array $params = [])
 * @method mixed GetExtensionCallRecording(array $params = [])
 * @method mixed SetExtensionCallRecording(array $params = [])
 * @method mixed GetExtensionPhoneBook(array $params = [])
 * @method mixed SetExtensionPhoneBook(array $params = [])
 * @method mixed GetExtensionForwarding(array $params = [])
 * @method mixed SetExtensionForwarding(array $params = [])
 * @method mixed GetExtensionSounds(array $params = [])
 * @method mixed GetExtensionCallScreening(array $params = [])
 *
 * ─── Global Operations (2 operations) ────────────────────────────────────
 * @method mixed GetTimezones(array $params = [])
 * @method mixed GetLanguages(array $params = [])
 *
 * ─── Organization (22 operations) ────────────────────────────────────────
 * @method mixed GetOrganizations(array $params = [])
 * @method mixed AddOrganization(array $params = [])
 * @method mixed UpdateOrganization(array $params = [])
 * @method mixed RemoveOrganization(array $params = [])
 * @method mixed GetOrganizationDetails(array $params = [])
 * @method mixed GetOrganizationPermissions(array $params = [])
 * @method mixed SetOrganizationPermissions(array $params = [])
 * @method mixed GetOrganizationCallCosts(array $params = [])
 * @method mixed SetOrganizationCallCosts(array $params = [])
 * @method mixed GetOrganizationBillingPackage(array $params = [])
 * @method mixed SetOrganizationBillingPackage(array $params = [])
 *
 * ─── PBX (80 operations) ─────────────────────────────────────────────────
 * @method mixed GetQueues(array $params = [])
 * @method mixed AddQueue(array $params = [])
 * @method mixed UpdateQueue(array $params = [])
 * @method mixed RemoveQueue(array $params = [])
 * @method mixed GetQueueDetails(array $params = [])
 * @method mixed GetQueueAgents(array $params = [])
 * @method mixed SetQueueAgents(array $params = [])
 * @method mixed GetIVRs(array $params = [])
 * @method mixed AddIVR(array $params = [])
 * @method mixed UpdateIVR(array $params = [])
 * @method mixed RemoveIVR(array $params = [])
 * @method mixed GetIVRDetails(array $params = [])
 * @method mixed GetConferences(array $params = [])
 * @method mixed AddConference(array $params = [])
 * @method mixed UpdateConference(array $params = [])
 * @method mixed RemoveConference(array $params = [])
 * @method mixed GetPhoneBooks(array $params = [])
 * @method mixed AddPhoneBook(array $params = [])
 * @method mixed UpdatePhoneBook(array $params = [])
 * @method mixed RemovePhoneBook(array $params = [])
 * @method mixed GetSounds(array $params = [])
 * @method mixed AddSound(array $params = [])
 * @method mixed RemoveSound(array $params = [])
 *
 * ─── Report (6 operations) ───────────────────────────────────────────────
 * @method mixed GetCallReport(array $params = [])
 * @method mixed GetCallReportDetails(array $params = [])
 * @method mixed GetCallCostReport(array $params = [])
 * @method mixed GetCallCostReportDetails(array $params = [])
 * @method mixed GetChargedCallReport(array $params = [])
 * @method mixed GetSIPReport(array $params = [])
 *
 * ─── Service Provider (20 operations) ────────────────────────────────────
 * @method mixed GetServiceProviders(array $params = [])
 * @method mixed AddServiceProvider(array $params = [])
 * @method mixed UpdateServiceProvider(array $params = [])
 * @method mixed RemoveServiceProvider(array $params = [])
 * @method mixed GetServiceProviderDetails(array $params = [])
 * @method mixed GetServiceProviderPermissions(array $params = [])
 * @method mixed SetServiceProviderPermissions(array $params = [])
 * @method mixed GetServiceProviderCallCosts(array $params = [])
 * @method mixed SetServiceProviderCallCosts(array $params = [])
 * @method mixed GetServiceProviderBillingPackage(array $params = [])
 * @method mixed SetServiceProviderBillingPackage(array $params = [])
 *
 * ─── User (22 operations) ────────────────────────────────────────────────
 * @method mixed GetUsers(array $params = [])
 * @method mixed AddUser(array $params = [])
 * @method mixed UpdateUser(array $params = [])
 * @method mixed RemoveUser(array $params = [])
 * @method mixed GetUserDetails(array $params = [])
 * @method mixed GetUserPermissions(array $params = [])
 * @method mixed SetUserPermissions(array $params = [])
 * @method mixed GetUserCallCosts(array $params = [])
 * @method mixed SetUserCallCosts(array $params = [])
 * @method mixed GetUserGroups(array $params = [])
 * @method mixed AddUserGroup(array $params = [])
 * @method mixed RemoveUserGroup(array $params = [])
 */
class VoipNowSoapClient
{
    protected Repository $config;
    protected SoapAdapter $adapter;
    protected ?SoapAdapter $connection = null;

    public function __construct(Repository $config, SoapAdapter $adapter)
    {
        $this->config  = $config;
        $this->adapter = $adapter;
    }

    protected function getConnection(): SoapAdapter
    {
        if ($this->connection === null) {
            $this->connection = $this->adapter->connect($this->getConfigurations());
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

    /**
     * Call any SystemAPI SOAP operation by name.
     *
     * @param  string  $method     The exact SystemAPI operation name, e.g. 'GetOrganizations'
     * @param  array   $params     Parameters to pass to the SOAP method
     */
    public function call(string $method, array $params = []): mixed
    {
        return $this->getConnection()->$method($params);
    }

    /**
     * Magic method — routes $client->GetOrganizations($params) directly
     * to the underlying SoapClient via SoapAdapter::__call().
     */
    public function __call(string $method, array $arguments): mixed
    {
        $params = $arguments[0] ?? [];
        return $this->call($method, $params);
    }
}
