<?php

namespace Liquido\PayIn\Block\Brl;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Checkout\Model\Session;

use \Liquido\PayIn\Service\Brl\LiquidoBrlPixPayInService;
use \Liquido\PayIn\Helper\Brl\LiquidoBrlConfigData;

class LiquidoBrlPixCode extends Template
{

    private $pixCode = null;
    private $orderId = null;
    private $errorMsg = null;
    private $hasFailed = false;

    protected $checkoutSession;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        ManagerInterface $messageManager,
        LiquidoBrlConfigData $liquidoConfig,
        LiquidoBrlPixPayInService $pixPayInService,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;

        // validating some inputs
        $this->orderId = $this->getIncrementId();
        if ($this->orderId == null) {
            $errorMessage = __('Erro ao obter o identificador do pedido.');
            $this->hasFailed = true;
        }

        $grandTotal = $this->getGrandTotal($this->orderId);
        if ($grandTotal == 0) {
            $errorMessage = __('O valor da compra deve ser maior que R$0,00.');
            $this->hasFailed = true;
        }

        if ($this->hasFailed) {
            $this->messageManager->addErrorMessage($errorMessage);
            return null;
        }

        $customerEmail = $this->getCustomerEmail();

        $callbackUrl = $liquidoConfig->getCallbackUrl();

        // init the PIX generation in Liquido Virgo API
        $this->pixCode = $pixPayInService->createLiquidoPixPayIn(
            $this->orderId,
            $customerEmail,
            $grandTotal,
            $callbackUrl
        );

        $successMessage = __('PIX Code gerado.');
        $this->messageManager->addSuccessMessage($successMessage);
    }

    public function getPixCode()
    {
        return $this->pixCode;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    public function hasFailed()
    {
        return $this->hasFailed;
    }

    private function getCustomerEmail()
    {
        try {
            $orderObj = $this->checkoutSession->getLastRealOrder();
            return $orderObj->getCustomerEmail();
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getIncrementId()
    {
        try {
            $orderObj = $this->checkoutSession->getLastRealOrder();
            return $orderObj->getIncrementId();
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getGrandTotal()
    {
        try {
            $orderObj = $this->checkoutSession->getLastRealOrder();
            return $orderObj->getGrandTotal() * 100;
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }
}
