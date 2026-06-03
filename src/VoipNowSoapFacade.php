<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow;

use Illuminate\Support\Facades\Facade;

/**
 * VoipNow SOAP Facade — SystemAPI (SOAP)
 *
 * For account provisioning operations: organizations, extensions,
 * users, billing, charging plans, PBX, and reporting.
 *
 * @see \HyderKamran\VoipNow\VoipNowSoapClient
 * @see https://wiki.4psa.com/VoipNow/Developer-Guide/SystemAPI/operations.html
 *
 * ─── Account ──────────────────────────────────────────────────────────────
 * @method static mixed GetSPBrandingColors(array $params = [])
 * @method static mixed SetSPBrandingColors(array $params = [])
 * @method static mixed GetAccountBillingPackage(array $params = [])
 * @method static mixed SetAccountBillingPackage(array $params = [])
 * @method static mixed GetAccountPermissions(array $params = [])
 * @method static mixed SetAccountPermissions(array $params = [])
 *
 * ─── Organization ─────────────────────────────────────────────────────────
 * @method static mixed GetOrganizations(array $params = [])
 * @method static mixed AddOrganization(array $params = [])
 * @method static mixed UpdateOrganization(array $params = [])
 * @method static mixed RemoveOrganization(array $params = [])
 * @method static mixed GetOrganizationDetails(array $params = [])
 * @method static mixed GetOrganizationPermissions(array $params = [])
 * @method static mixed SetOrganizationPermissions(array $params = [])
 * @method static mixed GetOrganizationCallCosts(array $params = [])
 * @method static mixed SetOrganizationCallCosts(array $params = [])
 *
 * ─── Service Provider ─────────────────────────────────────────────────────
 * @method static mixed GetServiceProviders(array $params = [])
 * @method static mixed AddServiceProvider(array $params = [])
 * @method static mixed UpdateServiceProvider(array $params = [])
 * @method static mixed RemoveServiceProvider(array $params = [])
 * @method static mixed GetServiceProviderDetails(array $params = [])
 * @method static mixed GetServiceProviderPermissions(array $params = [])
 * @method static mixed SetServiceProviderPermissions(array $params = [])
 *
 * ─── Extension ────────────────────────────────────────────────────────────
 * @method static mixed GetExtensions(array $params = [])
 * @method static mixed AddExtension(array $params = [])
 * @method static mixed UpdateExtension(array $params = [])
 * @method static mixed RemoveExtension(array $params = [])
 * @method static mixed GetExtensionDetails(array $params = [])
 * @method static mixed GetExtensionFeatures(array $params = [])
 * @method static mixed SetExtensionFeatures(array $params = [])
 * @method static mixed GetExtensionVoicemail(array $params = [])
 * @method static mixed SetExtensionVoicemail(array $params = [])
 * @method static mixed GetExtensionForwarding(array $params = [])
 * @method static mixed SetExtensionForwarding(array $params = [])
 *
 * ─── User ─────────────────────────────────────────────────────────────────
 * @method static mixed GetUsers(array $params = [])
 * @method static mixed AddUser(array $params = [])
 * @method static mixed UpdateUser(array $params = [])
 * @method static mixed RemoveUser(array $params = [])
 * @method static mixed GetUserDetails(array $params = [])
 * @method static mixed GetUserGroups(array $params = [])
 * @method static mixed AddUserGroup(array $params = [])
 * @method static mixed RemoveUserGroup(array $params = [])
 *
 * ─── Billing ──────────────────────────────────────────────────────────────
 * @method static mixed GetChargingPlans(array $params = [])
 * @method static mixed AddChargingPlan(array $params = [])
 * @method static mixed UpdateChargingPlan(array $params = [])
 * @method static mixed RemoveChargingPlan(array $params = [])
 * @method static mixed GetChargingPlanDetails(array $params = [])
 * @method static mixed GetCallingCards(array $params = [])
 * @method static mixed AddCallingCard(array $params = [])
 * @method static mixed RemoveCallingCard(array $params = [])
 *
 * ─── PBX ──────────────────────────────────────────────────────────────────
 * @method static mixed GetQueues(array $params = [])
 * @method static mixed AddQueue(array $params = [])
 * @method static mixed UpdateQueue(array $params = [])
 * @method static mixed RemoveQueue(array $params = [])
 * @method static mixed GetQueueAgents(array $params = [])
 * @method static mixed SetQueueAgents(array $params = [])
 * @method static mixed GetIVRs(array $params = [])
 * @method static mixed AddIVR(array $params = [])
 * @method static mixed UpdateIVR(array $params = [])
 * @method static mixed RemoveIVR(array $params = [])
 * @method static mixed GetConferences(array $params = [])
 * @method static mixed AddConference(array $params = [])
 * @method static mixed UpdateConference(array $params = [])
 * @method static mixed RemoveConference(array $params = [])
 *
 * ─── Channel ──────────────────────────────────────────────────────────────
 * @method static mixed GetChannels(array $params = [])
 * @method static mixed AddChannel(array $params = [])
 * @method static mixed UpdateChannel(array $params = [])
 * @method static mixed RemoveChannel(array $params = [])
 * @method static mixed GetChannelDetails(array $params = [])
 * @method static mixed GetChannelCallCosts(array $params = [])
 * @method static mixed SetChannelCallCosts(array $params = [])
 *
 * ─── Report ───────────────────────────────────────────────────────────────
 * @method static mixed GetCallReport(array $params = [])
 * @method static mixed GetCallCostReport(array $params = [])
 * @method static mixed GetCallReportDetails(array $params = [])
 * @method static mixed GetSIPReport(array $params = [])
 *
 * ─── Global Operations ────────────────────────────────────────────────────
 * @method static mixed GetTimezones(array $params = [])
 * @method static mixed GetLanguages(array $params = [])
 *
 * ─── Direct call ──────────────────────────────────────────────────────────
 * @method static mixed call(string $method, array $params = [])
 */
class VoipNowSoapFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'voipnow.soap';
    }
}
