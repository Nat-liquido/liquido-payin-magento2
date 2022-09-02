<?php

namespace Liquido\PayIn\Controller\LiquidoBRL;

use Magento\Framework\App\Action\Action;

class CreditCardForm extends Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
