<?php

namespace Liquido\PayIn\Gateway\Brl\ApiClient;

use \Magento\Framework\HTTP\Client\Curl;
use \Liquido\PayIn\Helper\Brl\LiquidoBrlConfigData;

class LiquidoBrlBoletoClient
{

    private const BOLETO_ENDPOINT = "/v1/payments/charges/boleto";
    private const BOLETO_PDF_ENDPOINT = "/v1/payments/files/boleto/pdf/";

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

    public function createBoletoPayIn($data)
    {
        $url = $this->liquidoConfig->getVirgoBaseUrl() . $this::BOLETO_ENDPOINT;

        try {
            $jsonData = json_encode($data);
            $this->curl->post($url, $jsonData);
            $result = $this->curl->getBody();
            return $result;
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public function getBoletoPdfUrl($idempotencyKey)
    {
        $url = $this->liquidoConfig->getVirgoBaseUrl() . $this::BOLETO_PDF_ENDPOINT . $idempotencyKey;

        try {
            $this->curl->get($url);
            $result = $this->curl->getBody();
            return $result;
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
