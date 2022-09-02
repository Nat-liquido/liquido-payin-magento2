<?php

namespace Liquido\PayIn\Model\Brl;

use \Magento\Framework\Webapi\Rest\Request;
use \Magento\Framework\Event\ManagerInterface as EventManager;

use Liquido\PayIn\Util\Brl\LiquidoBrlPayInStatus;

class LiquidoBrlWebhook
{

	protected $request;
	private $eventManager;

	public function __construct(
		Request $request,
		EventManager $eventManager
	) {
		$this->request = $request;
		$this->eventManager = $eventManager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function processLiquidoBrlCallbackRequest()
	{

		// *** TO DO: get headers from request

		$body = $this->request->getBodyParams();

		$eventType = $body["eventType"];
		// if $eventType == SOMETHING { do something... }

		// if "idempotencyKey" not in $body { do something... }
		// if "transferStatus" not in $body { do something... }
		$idempotencyKey = $body["data"]["chargeDetails"]["idempotencyKey"];
		$transferStatus = $body["data"]["chargeDetails"]["transferStatus"];

		// $orderData = new DataObject(array(
		// 	'idempotencyKey' => $idempotencyKey,
		// 	'transferStatus' => $transferStatus,
		// ));

		$newStatus = LiquidoBrlPayInStatus::mapToMagentoSaleOrderStatus($transferStatus);

		$this->eventManager->dispatch('liquido_brl_update_order_in_database', [
			'incrementId' => $idempotencyKey,
			'newStatus' => $newStatus
		]);

		return [[
			"status" => 200,
			"message" => "received"
		]];
	}
}
