<?php

namespace Liquido\PayIn\Gateway\Brl\ApiClient;

use \Magento\Framework\HTTP\Client\Curl;
use \Liquido\PayIn\Helper\Brl\LiquidoBrlConfigData;

class LiquidoBrlAuthClient
{

    private const GRANT_TYPE = "client_credentials";

    protected $curl;
    protected $formData;
    protected $liquidoConfig;

    /** *** Dependency Injection is not working here */
    // public function __construct(
    //     Curl $_curl,
    //     LiquidoBrlConfigData $liquidoConfig
    // ) {
    //     $this->curl = $_curl;
    //     $this->curl->addHeader("Content-Type", "application/x-www-form-urlencoded");
    //     $this->formData = [
    //         "client_id" => $liquidoConfig->getClientId(),
    //         "client_secret" => $liquidoConfig->getClientSecret(),
    //         "grant_type" => LiquidoBrlAuthClient::GRANT_TYPE,
    //     ];
    // }

    public function __construct()
    {
        $this->curl = new Curl;
        $this->curl->addHeader("Content-Type", "application/x-www-form-urlencoded");
        $this->liquidoConfig = new LiquidoBrlConfigData;
        $this->formData = [
            "client_id" => $this->liquidoConfig->getClientId(),
            "client_secret" => $this->liquidoConfig->getClientSecret(),
            "grant_type" => LiquidoBrlAuthClient::GRANT_TYPE,
        ];
    }

    public function authenticate()
    {
        try {
            $this->curl->post($this->liquidoConfig->getAuthUrl(), $this->formData);
            $result = $this->curl->getBody();
            return json_decode($result);
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
