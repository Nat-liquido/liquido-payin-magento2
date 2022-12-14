<?php

namespace Liquido\PayIn\Controller\LiquidoBRL;

use \Magento\Framework\App\ActionInterface;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\DataObject;
use \Psr\Log\LoggerInterface;

use \Liquido\PayIn\Helper\Brl\LiquidoBrlOrderData;
use \Liquido\PayIn\Service\Brl\LiquidoBrlPixPayInService;
use \Liquido\PayIn\Model\Brl\LiquidoBrlPayInSession;
use \Liquido\PayIn\Util\Brl\LiquidoBrlPayInStatus;
use \Liquido\PayIn\Util\Brl\LiquidoBrlPaymentMethodType;
use \Liquido\PayIn\Helper\Brl\LiquidoBrlSalesOrderHelper;

class PixCode implements ActionInterface
{
    private PageFactory $resultPageFactory;
    private ManagerInterface $messageManager;
    private LoggerInterface $logger;
    protected LiquidoBrlPayInSession $payInSession;
    private LiquidoBrlOrderData $liquidoOrderData;
    private LiquidoBrlPixPayInService $pixPayInService;
    private LiquidoBrlSalesOrderHelper $liquidoSalesOrderHelper;
    private DataObject $pixInputData;
    private DataObject $pixResultData;
    private String $errorMessage;

    public function __construct(
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        LiquidoBrlPayInSession $payInSession,
        LiquidoBrlOrderData $liquidoOrderData,
        LiquidoBrlPixPayInService $pixPayInService,
        LiquidoBrlSalesOrderHelper $liquidoSalesOrderHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->payInSession = $payInSession;
        $this->liquidoOrderData = $liquidoOrderData;
        $this->pixPayInService = $pixPayInService;
        $this->liquidoSalesOrderHelper = $liquidoSalesOrderHelper;
        $this->pixInputData = new DataObject(array());
        $this->pixResultData = new DataObject(array());
        $this->errorMessage = "";
    }

    private function validateInputPixData()
    {

        $orderId = $this->liquidoOrderData->getIncrementId();
        if ($orderId == null) {
            $this->errorMessage = __('Erro ao obter o n??mero do pedido.');
            return false;
        }

        $grandTotal = $this->liquidoOrderData->getGrandTotal();
        if ($grandTotal == 0 || null) {
            $this->errorMessage = __('O valor da compra deve ser maior que R$0,00.');
            return false;
        }

        $customerEmail = $this->liquidoOrderData->getCustomerEmail();
        if ($customerEmail == null) {
            $this->errorMessage = __('Erro ao obter o email do cliente.');
            return false;
        }

        $this->pixInputData = new DataObject(array(
            'orderId' => $orderId,
            'grandTotal' => $grandTotal,
            'customerEmail' => $customerEmail
        ));

        return true;
    }

    private function managePixResponse($pixResponse)
    {
        if (
            $pixResponse != null
            && property_exists($pixResponse, 'transferStatusCode')
            && $pixResponse->transferStatusCode == 200
        ) {
            if (
                $pixResponse->paymentMethod == LiquidoBrlPaymentMethodType::PIX_STATIC_QR
                && $pixResponse->transferStatus == LiquidoBrlPayInStatus::IN_PROGRESS
            ) {
                $successMessage = __('C??digo PIX gerado.');
                $this->messageManager->addSuccessMessage($successMessage);
            }

            if ($pixResponse->transferStatus == LiquidoBrlPayInStatus::SETTLED) {
                $successMessage = __('Pagamento j?? aprovado.');
                $this->messageManager->addSuccessMessage($successMessage);
            }

            $this->pixResultData->setData('paymentMethod', $pixResponse->paymentMethod);

            if ($pixResponse->paymentMethod == LiquidoBrlPaymentMethodType::PIX_STATIC_QR) {
                $this->pixResultData->setData('pixCode', $pixResponse->transferDetails->pix->qrCode);
            }

            $this->pixResultData->setData('transferStatus', $pixResponse->transferStatus);
        } else {
            $this->pixResultData->setData('hasFailed', true);

            $errorMsg = "Falha.";
            if (
                $pixResponse != null
                && property_exists($pixResponse, 'status')
                && $pixResponse->status != 200
            ) {
                $errorMsg .= " ($pixResponse->status - $pixResponse->error)";
            } else if (
                $pixResponse != null
                && property_exists($pixResponse, 'transferStatusCode')
                && $pixResponse->transferStatusCode != 200
            ) {
                $errorMsg .= " ($pixResponse->transferStatusCode - $pixResponse->transferErrorMsg)";
            } else {
                $errorMsg .= " (Erro ao tentar gerar o pagamento)";
            }

            $this->messageManager->addErrorMessage($errorMsg);
        }
    }

    public function execute()
    {

        $className = static::class;
        $this->logger->info("###################### BEGIN ######################");
        $this->logger->info("[ {$className} Controller ]: PIX Request received.");

        /**
         * Data to pass from Controller to Block
         */
        $this->pixResultData = new DataObject(array(
            'orderId' => null,
            'pixCode' => null,
            'transferStatus' => null,
            'paymentMethod' => null,
            'hasFailed' => false
        ));

        $areValidData = $this->validateInputPixData();
        if (!$areValidData) {
            $this->pixResultData->setData('hasFailed', true);
            $this->messageManager->addErrorMessage($this->errorMessage);
            $this->logger->warning("[ {$className} Controller ]: Invalid input data:", (array) $this->pixInputData);
            $this->logger->warning("[ {$className} Controller ]: Error message: {$this->errorMessage}");
        } else {

            $this->logger->info("[ {$className} Controller ]: Valid input data:", (array) $this->pixInputData);

            $orderId = $this->pixInputData->getData("orderId");

            $this->pixResultData->setData('orderId', $orderId);

            /**
             * Don't generate a new idempotency key if a request was already done successfuly before.
             */
            $liquidoIdempotencyKey = $this->liquidoSalesOrderHelper
                ->getAlreadyRegisteredIdempotencyKey($orderId);
            if ($liquidoIdempotencyKey == null) {
                $liquidoIdempotencyKey = $this->liquidoOrderData->generateUniqueToken();
            }

            $this->pixInputData->setData('idempotencyKey', $liquidoIdempotencyKey);

            $pixResponse = $this->pixPayInService->createLiquidoPixPayIn(
                $this->pixInputData
            );

            $this->managePixResponse($pixResponse);

            if (
                $pixResponse != null
                && $pixResponse->transferStatusCode == 200
            ) {
                $orderData = new DataObject(array(
                    "orderId" => $orderId,
                    "idempotencyKey" => $liquidoIdempotencyKey,
                    "transferStatus" => $pixResponse->transferStatus,
                    "paymentMethod" => $pixResponse->paymentMethod
                ));
                $this->liquidoSalesOrderHelper->createOrUpdateLiquidoSalesOrder($orderData);
            }
        }

        $this->logger->info("[ {$className} Controller ]: Result data:", (array) $this->pixResultData);
        $this->logger->info("###################### END ######################");

        $this->payInSession->setData("pixResultData", $this->pixResultData);

        return $this->resultPageFactory->create();
    }
}
