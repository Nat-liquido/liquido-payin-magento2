<?php

namespace Liquido\PayIn\Service\Brl;

use Liquido\PayIn\Gateway\Brl\ApiClient\LiquidoBrlCreditCardClient;

class LiquidoBrlCreditCardPayInService
{
    private $liquidoCreditCardPayInClient;

    public function __construct(
        LiquidoBrlCreditCardClient $liquidoCreditCardPayInClient
    ) {
        $this->liquidoCreditCardPayInClient = $liquidoCreditCardPayInClient;
    }

    public function createCreditCardPayIn(
        $liquidoCreditCardData
    ) {
        try {

            $data = [
                "idempotencyKey" => $liquidoCreditCardData['orderId'],
                "amount" => $liquidoCreditCardData['grandTotal'],
                "currency" => "BRL",
                "country" => "BR",
                "paymentMethod" => "CREDIT_CARD",
                "paymentFlow" => "DIRECT",
                "payer" => [
                    "name" => $liquidoCreditCardData['customerName'],
                    "email" => $liquidoCreditCardData['customerEmail']
                ],
                "card" => [
                    "cardHolderName" => $liquidoCreditCardData['cardName'],
                    "cardNumber" => $liquidoCreditCardData['cardNumber'],
                    "expirationMonth" => $liquidoCreditCardData['cardMonth'],
                    "expirationYear" => $liquidoCreditCardData['cardYear'],
                    "cvc" => $liquidoCreditCardData['cardCVV']
                ],
                //  "riskData" => [
                //      "ipAddress" => "192.168.0.1"
                //  ],
                "description" => "Module Magento 2 Credit Card Request",
                "installments" => $liquidoCreditCardData['cardInstallments'],
                "callbackUrl" => $liquidoCreditCardData['callbackURL']
            ];

            $creditCardJsonResponse = $this->liquidoCreditCardPayInClient->createCreditCardPayIn($data);
            $creditCardResponse = json_decode($creditCardJsonResponse);

            return $creditCardResponse;
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
