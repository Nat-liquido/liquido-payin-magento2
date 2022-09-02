<?php

namespace Liquido\PayIn\Block\Brl;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Checkout\Model\Session;

use \Liquido\PayIn\Service\Brl\LiquidoBrlCreditCardPayInService;
use \Liquido\PayIn\Helper\Brl\LiquidoBrlConfigData;

class LiquidoBrlCreditCardForm extends Template
{
    private $orderTotal = null;

    protected $checkoutSession;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;

        $this->orderTotal = $this->getGrandTotal();
    }

    public function getCardPayInMethodName()
    {
        // return LiquidoPayInMethod::CREDIT_CARD;
        return "Cartão de Crédito";
    }

    public function getOrderTotal()
    {
        return $this->orderTotal;
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
