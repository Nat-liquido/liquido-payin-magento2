<?php

namespace Liquido\PayIn\Gateway\Brl\ApiClient;

use \Magento\Framework\HTTP\Client\Curl;

use \Liquido\PayIn\Helper\Brl\LiquidoBrlConfigData;

class LiquidoBrlPixClient
{
    private const PIX_ENDPOINT = "/v1/payments/charges/pix";

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

    public function createPixPayIn($data)
    {
        $url = $this->liquidoConfig->getVirgoBaseUrl() . $this::PIX_ENDPOINT;

        try {
            $jsonData = json_encode($data);
            $this->curl->post($url, $jsonData);
            $result = $this->curl->getBody();
            return $result;
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
