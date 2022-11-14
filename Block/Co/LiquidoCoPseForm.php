<?php

namespace Liquido\PayIn\Block\Co;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Checkout\Model\Session;

use \Liquido\PayIn\Util\Co\LiquidoCoPayInMethod;
use \Liquido\PayIn\Helper\Brl\LiquidoBrlOrderData;

class LiquidoCoPseForm extends Template
{

    protected Session $checkoutSession;
    private LiquidoBrlOrderData $liquidoOrderData;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        LiquidoBrlOrderData $liquidoOrderData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->liquidoOrderData = $liquidoOrderData;
    }

    public function getPsePayInMethodName()
    {
        return LiquidoCoPayInMethod::PSE["title"];
    }

    public function getPseFinancialInstitutionsList()
    {
        $financialInstitutions = [
            [
                "financial_institution_code" => "1059",
                "financial_institution_name" => "BANCAMIA S.A."
            ],
            [
                "financial_institution_code" => "1040",
                "financial_institution_name" => "BANCO AGRARIO"
            ],
            [
                "financial_institution_code" => "1052",
                "financial_institution_name" => "BANCO AV VILLAS"
            ],
            [
                "financial_institution_code" => "1013",
                "financial_institution_name" => "BANCO BBVA COLOMBIA S.A."
            ],
            [
                "financial_institution_code" => "1032",
                "financial_institution_name" => "BANCO CAJA SOCIAL"
            ],
            [
                "financial_institution_code" => "1066",
                "financial_institution_name" => "BANCO COOPERATIVO COOPCENTRAL"
            ],
            [
                "financial_institution_code" => "1558",
                "financial_institution_name" => "BANCO CREDIFINANCIERA"
            ],
            [
                "financial_institution_code" => "1051",
                "financial_institution_name" => "BANCO DAVIVIENDA"
            ],
            [
                "financial_institution_code" => "1001",
                "financial_institution_name" => "BANCO DE BOGOTA"
            ],
            [
                "financial_institution_code" => "1023",
                "financial_institution_name" => "BANCO DE OCCIDENTE"
            ],
            [
                "financial_institution_code" => "1062",
                "financial_institution_name" => "BANCO FALABELLA "
            ],
            [
                "financial_institution_code" => "1012",
                "financial_institution_name" => "BANCO GNB SUDAMERIS"
            ],
            [
                "financial_institution_code" => "1006",
                "financial_institution_name" => "BANCO ITAU"
            ],
            [
                "financial_institution_code" => "1060",
                "financial_institution_name" => "BANCO PICHINCHA S.A."
            ],
            [
                "financial_institution_code" => "1002",
                "financial_institution_name" => "BANCO POPULAR"
            ],
            [
                "financial_institution_code" => "1065",
                "financial_institution_name" => "BANCO SANTANDER COLOMBIA"
            ],
            [
                "financial_institution_code" => "1069",
                "financial_institution_name" => "BANCO SERFINANZA"
            ],
            [
                "financial_institution_code" => "1007",
                "financial_institution_name" => "BANCOLOMBIA"
            ],
            [
                "financial_institution_code" => "1061",
                "financial_institution_name" => "BANCOOMEVA S.A."
            ],
            [
                "financial_institution_code" => "1283",
                "financial_institution_name" => "CFA COOPERATIVA FINANCIERA"
            ],
            [
                "financial_institution_code" => "1009",
                "financial_institution_name" => "CITIBANK "
            ],
            [
                "financial_institution_code" => "1370",
                "financial_institution_name" => "COLTEFINANCIERA"
            ],
            [
                "financial_institution_code" => "1292",
                "financial_institution_name" => "CONFIAR COOPERATIVA FINANCIERA"
            ],
            [
                "financial_institution_code" => "1289",
                "financial_institution_name" => "COTRAFA"
            ],
            [
                "financial_institution_code" => "1551",
                "financial_institution_name" => "DAVIPLATA"
            ],
            [
                "financial_institution_code" => "1303",
                "financial_institution_name" => "GIROS Y FINANZAS"
            ],
            [
                "financial_institution_code" => "1507",
                "financial_institution_name" => "NEQUI"
            ],
            [
                "financial_institution_code" => "1151",
                "financial_institution_name" => "RAPPIPAY"
            ],
            [
                "financial_institution_code" => "1019",
                "financial_institution_name" => "SCOTIABANK COLPATRIA"
            ],
            [
                "financial_institution_code" => "1637",
                "financial_institution_name" => "IRIS"
            ],
            [
                "financial_institution_code" => "1291",
                "financial_institution_name" => "COOFINEP COOPERATIVA FINANCIERA"
            ],
            [
                "financial_institution_code" => "1070",
                "financial_institution_name" => "LULO BANK"
            ],
            [
                "financial_institution_code" => "1802",
                "financial_institution_name" => "DING TECNIPAGOS S.A."
            ]
        ];

        return $financialInstitutions;
    }
}
