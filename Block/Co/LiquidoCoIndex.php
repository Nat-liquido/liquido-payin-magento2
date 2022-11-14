<?php

namespace Liquido\PayIn\Block\Co;

use \Magento\Framework\View\Element\Template;

use \Liquido\PayIn\Util\Co\LiquidoCoPayInMethod;
use \Liquido\PayIn\Util\Co\LiquidoCoPayInViewRoute;

class LiquidoCoIndex extends Template
{

    public function getLiquidoColombiaPayInMethods()
    {
        $colombia_payin_methods = [
            LiquidoCoPayInMethod::CREDIT_CARD,
            LiquidoCoPayInMethod::CASH,
            LiquidoCoPayInMethod::PSE
        ];
        return $colombia_payin_methods;
    }

    public function getPayInMethodViewRoute($_payin_method_title)
    {
        switch ($_payin_method_title) {
            case LiquidoCoPayInMethod::CASH["title"]:
                return LiquidoCoPayInViewRoute::CASH;
                break;
            case LiquidoCoPayInMethod::PSE["title"]:
                return LiquidoCoPayInViewRoute::PSE;
                break;
            case LiquidoCoPayInMethod::CREDIT_CARD["title"]:
                return LiquidoCoPayInViewRoute::CREDIT_CARD;
                break;
            default:
                return "#";
        }
    }
}
