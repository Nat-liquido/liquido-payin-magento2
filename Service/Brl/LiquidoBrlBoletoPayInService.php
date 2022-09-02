<?php

namespace Liquido\PayIn\Service\Brl;

use Liquido\PayIn\Gateway\Brl\ApiClient\LiquidoBrlBoletoClient;
use Liquido\PayIn\Util\Brl\LiquidoBrlPayInStatus;

class LiquidoBrlBoletoPayInService
{

    private $liquidoBoletoClient;

    public function __construct(
        LiquidoBrlBoletoClient $liquidoBoletoClient
    ) {
        $this->liquidoBoletoClient = $liquidoBoletoClient;
    }

    public function createLiquidoBoletoPayIn(
        $incrementId,
        $customerCpf,
        $amountTotal,
        $billingAddress,
        $callbackUrl
    ) {
        try {

            $payerName = $billingAddress->getFirstname() . " " . $billingAddress->getLastname();

            $street = $billingAddress->getStreet()[0];
            if (count($billingAddress->getStreet()) == 2) {
                $street .= " - " . $billingAddress->getStreet()[1];
            } else if (count($billingAddress->getStreet()) == 3) {
                $street .= " - " . $billingAddress->getStreet()[1] . $billingAddress->getStreet()[2];
            }

            // Boleto date expiration (timestamp)
            $dateDeadline = date('Y-m-d H:i:s', strtotime('+1 day', time()));
            $timestampDeadline = strtotime($dateDeadline);

            $data = [
                "idempotencyKey" => $incrementId,
                "amount" => $amountTotal,
                "currency" => "BRL",
                "country" => "BR",
                "paymentMethod" => "BOLETO",
                "paymentFlow" => "DIRECT",
                "callbackUrl" => $callbackUrl,
                "payer" => [
                    "name" => $payerName,
                    "document" => [
                        "documentId" => $customerCpf,
                        "type" => "CPF"
                    ],
                    "billingAddress" => [
                        "zipCode" => $billingAddress->getPostcode(),
                        "state" => $billingAddress->getRegionCode(),
                        "city" => $billingAddress->getCity(),
                        "district" => "Unknown",
                        "street" => $street,
                        "number" => "Unknown",
                        "country" => $billingAddress->getCountryId()
                    ],
                    "email" => $billingAddress->getEmail()
                ],
                "paymentTerm" => [
                    "paymentDeadline" => $timestampDeadline
                ],
            ];

            $boletoJsonResponse = $this->liquidoBoletoClient->createBoletoPayIn($data);
            $boletoResponse = json_decode($boletoJsonResponse);
            if ($boletoResponse->transferStatus == LiquidoBrlPayInStatus::FAILED) {
                echo $boletoResponse->transferStatus . " - " . $boletoResponse->transferErrorMsg;
            } else {
                return $boletoResponse->transferDetails->boleto;
            }
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public function getLiquidoBoletoPdfUrl(
        $idempotencyKey
    ) {
        try {
            $boletoPdfJsonResponse = $this->liquidoBoletoClient->getBoletoPdfUrl(
                $idempotencyKey
            );
            return $boletoPdfJsonResponse;
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
