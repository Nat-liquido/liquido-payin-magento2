<?php

namespace Liquido\PayIn\Block\Brl;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Checkout\Model\Session;

use \Liquido\PayIn\Service\Brl\LiquidoBrlCreditCardPayInService;
use \Liquido\PayIn\Helper\Brl\LiquidoBrlConfigData;

class LiquidoBrlCreditCard extends Template
{
    private $orderId;
    private $errorMsg = null;
    private $hasFailed = false;
    private $orderTotal;
    private $installments;

    protected $checkoutSession;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        ManagerInterface $messageManager,
        LiquidoBrlConfigData $liquidoConfig,
        LiquidoBrlCreditCardPayInService $creditCardPayInService,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;

        $this->orderId = $this->getIncrementId();
        if ($this->orderId == null) {
            $errorMessage = __('Erro ao obter o identificador do pedido.');
            $this->hasFailed = true;
        }

        $this->orderTotal = $this->getGrandTotal($this->orderId);
        if ($this->orderTotal == 0) {
            $errorMessage = __('O valor da compra deve ser maior que R$0,00.');
            $this->hasFailed = true;
        }

        $customerCardNumber = $this->getCustomerCardNumber();
        if ($customerCardNumber == null) {
            $errorMessage = __('Erro ao obter número do cartão de crédito.');
            $this->hasFailed = true;
        }

        $customerCardName = $this->getCustomerCardName();
        if ($customerCardName == null) {
            $errorMessage = __('Erro ao obter nome do titular do cartão de crédito.');
            $this->hasFailed = true;
        }

        $customerCardDate = $this->getCustomerCardDate();
        if ($customerCardDate == null) {
            $errorMessage = __('Erro ao obter data de vencimento do cartão de crédito.');
            $this->hasFailed = true;
        }
        $customerCardDateArray = explode('/', $customerCardDate);

        $customerCardCVV = $this->getCustomerCardCVV();
        if ($customerCardCVV == null) {
            $errorMessage = __('Erro ao obter código de segurança (CVV) do cartão de crédito.');
            $this->hasFailed = true;
        }

        if ($this->hasFailed) {
            $this->messageManager->addErrorMessage($errorMessage);
            return null;
        }

        $customerCardInstallments = $this->getCustomerCardInstallments();
        $customerEmail = $this->getCustomerEmail();
        $customerName = $this->getCustomerName();

        $callbackUrl = $liquidoConfig->getCallbackUrl();

        $liquidoCreditCardData = [
            "orderId" => $this->orderId,
            "grandTotal" => $this->orderTotal,
            "customerEmail" => $customerEmail,
            "customerName" => $customerName,
            "cardNumber" => $customerCardNumber,
            "cardName" => $customerCardName,
            "cardMonth" => $customerCardDateArray[0],
            "cardYear" => $customerCardDateArray[1],
            "cardCVV" => $customerCardCVV,
            "cardInstallments" => $customerCardInstallments,
            "callbackURL" => $callbackUrl
        ];

        $this->creditCardInfo = $creditCardPayInService->createCreditCardPayIn(
            $liquidoCreditCardData
        );

        $successMessage = __('Pagamento aprovado.');
        $this->messageManager->addSuccessMessage($successMessage);
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getOrderTotal()
    {
        return $this->orderTotal;
    }

    public function getInstallments()
    {
        $this->installments = $this->getInstallmentsValue();
        return $this->installments;
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

    private function getGrandTotal($incrementId)
    {
        try {
            $orderObj = $this->checkoutSession->getLastRealOrder();
            return $orderObj->getGrandTotal() * 100;
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getCustomerName()
    {
        try {
            $orderObj = $this->checkoutSession->getLastRealOrder();
            return $orderObj->getCustomerName();
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getInstallmentsValue()
    {
        try {
            $grandTotal = $this->orderTotal / 100;
            $installmentValue = $grandTotal / $_POST['card-installments'];
            $installmentDecimal = number_format($installmentValue, 2, ',', '.');
            $installmentInfo = $_POST['card-installments'] . "x de R$ " . $installmentDecimal;

            return $installmentInfo;
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getCustomerCardNumber()
    {
        try {
            return $this->getRequest()->getParam('card-number');
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getCustomerCardName()
    {
        try {
            return $this->getRequest()->getParam('card-name');
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getCustomerCardDate()
    {
        try {
            return $this->getRequest()->getParam('card-date');
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getCustomerCardCVV()
    {
        try {
            return $this->getRequest()->getParam('card-cvv');
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }

    private function getCustomerCardInstallments()
    {
        try {
            return $this->getRequest()->getParam('card-installments');
        } catch (\Exception $e) {
            echo $e;
            return null;
        }
    }
}
