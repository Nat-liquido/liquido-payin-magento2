<?php

namespace Liquido\PayIn\Service\Brl;

use Liquido\PayIn\Gateway\Brl\ApiClient\LiquidoBrlPixClient;

class LiquidoBrlPixPayInService
{
    private $liquidoPixPayInClient;

    public function __construct(
        LiquidoBrlPixClient $liquidoPixPayInClient
    ) {
        $this->liquidoPixPayInClient = $liquidoPixPayInClient;
    }

    public function createLiquidoPixPayIn(
        $incrementId,
        $customerEmail,
        $amountTotal,
        $callbackUrl
    ) {
        try {

            $data = [
                "idempotencyKey" => $incrementId,
                "amount" => $amountTotal,
                "currency" => "BRL",
                "country" => "BR",
                "paymentMethod" => "PIX_STATIC_QR",
                "paymentFlow" => "DIRECT",
                "callbackUrl" => $callbackUrl,
                "payer" => [
                    "email" => $customerEmail
                ]
            ];

            $pixJsonResponse = $this->liquidoPixPayInClient->createPixPayIn($data);
            $pixResponse = json_decode($pixJsonResponse);
            return $pixResponse->qrCode;
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
