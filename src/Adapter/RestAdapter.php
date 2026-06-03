<?php

namespace HyderKamran\VoipNow\Adapter;

use Auth;
use GuzzleHttp\Client as Guzzle;
use HyderKamran\VoipNow\Interface\ConnectorInterface;

class SoapAdapter implements ConnectorInterface
{
    protected $client;
    public function connect(array $config)
    {
        $this->config = $config;
        return $this->getAdapter();
    }

    private function getAdapter()
    {
        $wsdlDomain = $this->config['voip_domain'] . '/soap2/schema/latest/voipnowservice.wsdl';

        $wsdlOptions = [
            'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'connection_timeout' => 60,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
        ];

        $client = new \SoapClient($wsdlDomain, $wsdlOptions);

        $auth = new \stdClass();
        $auth->accessToken = $this->getToken();
        $authvalues = new \SoapVar($auth, SOAP_ENC_OBJECT, 'http://4psa.com/HeaderData.xsd/' . $this->config['voip_version']);

        $header = new \SoapHeader('http://4psa.com/HeaderData.xsd/' . $this->config['voip_version'], 'userCredentials', $authvalues, false);
        $client->__setSoapHeaders([$header]);

        return $client;
    }

    private function getToken()
    {
        $tokenInfo = $this->getTokenInfo();

        if (!isset($tokenInfo->voipnow_access_token) || $tokenInfo->voipnow_token_expired_at <= now()) {

            $client = new Guzzle;
            $request = $client->post($this->config['domain'] . '/oauth/token.php', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->config['voip_key'],
                    'client_secret' => $this->config['voip_secret'],
                ],
            ]);

            $result = json_decode($request->getBody()->getContents());

            $tokenInfo->voipnow_access_token = $result->access_token;
            $tokenInfo->voipnow_token_expires_in = $result->expires_in;
            $tokenInfo->voipnow_token_expired_at = now()->addSeconds($result->expires_in)->format('Y-m-d H:i:s');

            $this->storeTokenInfo($tokenInfo);

            return $tokenInfo->voipnow_access_token;
        }

        return $tokenInfo->voipnow_access_token;
    }

    private function storeTokenInfo($tokenInfo)
    {
        $userData = Auth::user();
        $userData->voipnow_access_token = $tokenInfo->voipnow_access_token;
        $userData->voipnow_token_expires_in = $tokenInfo->voipnow_token_expires_in;
        $userData->voipnow_token_expired_at = $tokenInfo->voipnow_token_expired_at;
        return $userData->save();
    }

    private function getTokenInfo()
    {
        if (Auth::guest()) {
            throw new \Exception('Please login to make this request.');
        }

        $currentUser = Auth::user();
        $tokenInfo = [
            'voipnow_access_token' => $currentUser->voipnow_access_token,
            'voipnow_token_expires_in' => $currentUser->voipnow_token_expires_in,
            'voipnow_token_expired_at' => $currentUser->voipnow_token_expired_at,
        ];

        return $tokenInfo;

    }

}