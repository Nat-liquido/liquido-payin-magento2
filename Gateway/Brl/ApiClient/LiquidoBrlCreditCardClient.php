<?php

namespace Liquido\PayIn\Gateway\Brl\ApiClient;

use \Magento\Framework\HTTP\Client\Curl;

use \Liquido\PayIn\Helper\Brl\LiquidoBrlConfigData;

class LiquidoBrlCreditCardClient 
{

    private const CREDIT_CARD_ENDPOINT = "/v1/payments/charges/card";

    protected $curl;
    protected $liquidoConfig;

    public function __construct(
        Curl $_curl,
        LiquidoBrlConfigData $liquidoConfig,
        LiquidoBrlAuthClient $liquidoAuthClient
    ) {
        $this->curl = $_curl;
        $this->liquidoConfig = $liquidoConfig;

        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("x-api-key", $this->liquidoConfig->getApiKey());

        $authResponse = $liquidoAuthClient->authenticate();
        $this->curl->addHeader("Authorization", "Bearer $authResponse->access_token");
    }

    public function createCreditCardPayIn($data)
    {
        $url = $this->liquidoConfig->getVirgoBaseUrl() . $this::CREDIT_CARD_ENDPOINT;
    
        try {
            $jsonData = json_encode($data);
            $this->curl->post($url, stripslashes($jsonData));
            $result = $this->curl->getBody();
            return $result;
        } catch (\Exception $e) {
            echo $e;
        }
    }
}