<?php

namespace Liquido\PayIn\Block\Brl;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Checkout\Model\Session;

use \Liquido\PayIn\Service\Brl\LiquidoBrlBoletoPayInService;
use \Liquido\PayIn\Helper\Brl\LiquidoBrlConfigData;

class LiquidoBrlBoleto extends Template
{
    private $boletoBarCode = null;
    private $boletoDigitalLine = null;
    private $boletoUrl = null;
    private $orderId = null;
    private $hasFailed = false;

    protected $checkoutSession;
    protected $messageManager;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        ManagerInterface $messageManager,
        LiquidoBrlBoletoPayInService $boletoPayInService,
        LiquidoBrlConfigData $liquidoConfig,
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

        $billingAddress = $this->getBillingAddress();
        if ($billingAddress == null) {
            $errorMessage = __('Erro ao obter o endereço de cobrança do pedido.');
            $this->hasFailed = true;
        }

        $customerCpf = $this->getCustomerCpf();
        if ($customerCpf == null) {
            $errorMessage = __('O CPF do cliente deve ser informado.');
            $this->hasFailed = true;
        }

        if ($this->hasFailed) {
            $this->messageManager->addErrorMessage($errorMessage);
            return null;
        }

        $callbackUrl = $liquidoConfig->getCallbackUrl();

        // init the Boleto generation in Liquido Virgo API
        $boletoObj = $boletoPayInService->createLiquidoBoletoPayIn(
            $this->orderId,
            $customerCpf,
            $grandTotal,
            $billingAddress,
            $callbackUrl
        );

        $this->boletoBarCode = $boletoObj->barcode;
        $this->boletoDigitalLine = $boletoObj->digitalLine;

        $this->boletoUrl = $boletoPayInService->getLiquidoBoletoPdfUrl(
            $this->orderId
        );

        $successMessage = __('Boleto gerado.');
        $this->messageManager->addSuccessMessage($successMessage);
    }

    public function getBoletoBarCode()
    {
        return $this->boletoBarCode;
    }

    public function getBoletoDigitalLine()
    {
        return $this->boletoDigitalLine;
    }

    public function getBoletoUrl()
    {
        return $this->boletoUrl;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function hasFailed()
    {
        return $this->hasFailed;
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

    private function getBillingAddress()
    {
        try {
            $orderObj = $this->checkoutSession->getLastRealOrder();
            return $orderObj->getBillingAddress();
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getCustomerCpf()
    {
        try {
            return $this->getRequest()->getParam('customer-cpf');
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }
}
