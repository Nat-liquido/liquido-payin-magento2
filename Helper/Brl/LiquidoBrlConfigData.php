<?php

namespace Liquido\PayIn\Helper\Brl;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\ObjectManager;

class LiquidoBrlConfigData extends AbstractHelper
{

    private const LIQUIDO_SANDBOX_AUTH_URL = "https://auth-dev.liquido.com/oauth2/token";
    private const LIQUIDO_SANDBOX_VIRGO_BASE_URL = "https://api-qa.liquido.com";

    private const LIQUIDO_PRODUCTION_AUTH_URL = "https://authsg.liquido.com/oauth2/token";
    private const LIQUIDO_PRODUCTION_VIRGO_BASE_URL = "https://api.liquido.com";

    protected $objectManager;

    public function __construct()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function getCallbackUrl()
    {
        $storeManager = $this->objectManager->get(
            '\Magento\Store\Model\StoreManagerInterface'
        );
        return $storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_WEB
        )  . "rest/V1/liquido-brl-webhook";
    }

    private function isProductionModeActived()
    {
        $path = "payment/liquido/production_mode";
        $isProductionModeActived = $this->objectManager->get(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->getValue($path);
        return $isProductionModeActived;
    }

    public function getAuthUrl()
    {
        if ($this->isProductionModeActived()) {
            return LiquidoBrlConfigData::LIQUIDO_PRODUCTION_AUTH_URL;
        }
        return LiquidoBrlConfigData::LIQUIDO_SANDBOX_AUTH_URL;
    }

    public function getVirgoBaseUrl()
    {
        if ($this->isProductionModeActived()) {
            return LiquidoBrlConfigData::LIQUIDO_PRODUCTION_VIRGO_BASE_URL;
        }
        return LiquidoBrlConfigData::LIQUIDO_SANDBOX_VIRGO_BASE_URL;
    }

    public function getClientId()
    {
        $path = "payment/liquido/sandbox_client_id";
        if ($this->isProductionModeActived()) {
            $path = "payment/liquido/prod_client_id";
        }
        $clientId = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($path);
        return $clientId;
    }

    public function getClientSecret()
    {
        $path = "payment/liquido/sandbox_client_secret";
        if ($this->isProductionModeActived()) {
            $path = "payment/liquido/prod_client_secret";
        }
        $clientSecret = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($path);
        return $clientSecret;
    }

    public function getApiKey()
    {
        $path = "payment/liquido/sandbox_api_key";
        if ($this->isProductionModeActived()) {
            $path = "payment/liquido/prod_api_key";
        }
        $clientSecret = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($path);
        return $clientSecret;
    }
}
