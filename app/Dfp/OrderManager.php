<?php

namespace App\Dfp;

require("../../vendor/autoload.php");

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\v201802\Order;
use Google\AdsApi\Dfp\v201802\OrderService;
use Google\AdsApi\Dfp\Util\v201802\StatementBuilder;
use Google\AdsApi\Dfp\v201802\ApproveOrders as ApproveOrdersAction;

class OrderManager extends DfpManager
{
	public function setUpOrder($orderName, $advertiserId, $traffickerId)
	{	
		if(empty(($foo = $this->getOrder($orderName))))
		{
			$foo = $this->createOrder($orderName, $advertiserId, $traffickerId);
		}
		return $foo[0]['orderId'];
	}




	public function getAllOrders()
	{
		$output = [];
		$orderService = $this->dfpServices->get($this->session, OrderService::class);

		$statementBuilder = (new StatementBuilder())->orderBy('id ASC');
		$data = $orderService->getOrdersByStatement($statementBuilder->toStatement());
		if($data->getResults() == null)
		{
			return $output;
		}
		foreach ($data->getResults() as $order) {
		    $foo = array(
		  		"orderId" => $order->getId(),
		        "orderName" => $order->getName(),
		        "orderAdvertiserId" => $order->getAdvertiserId(),
		        "salespersonId" => $order->getSalespersonId(),
		        "traffickerId" => $order->getTraffickerId()
		    );
		    array_push($output, $foo);
		}
		return $output;
	}

	public function approveOrder($orderId)
	{
		$orderService = $this->dfpServices->get($this->session, OrderService::class);
		$statementBuilder = (new StatementBuilder())
			->where('id = :id')
			->withBindVariableValue('id', $orderId);
		
        // Create and perform action.
        $action = new ApproveOrdersAction();
        $result = $orderService->performOrderAction(
            $action,
            $statementBuilder->toStatement()
        );

	}

	public function getOrder($orderName)
	{
		$output = [];
		$orderService = $this->dfpServices->get($this->session, OrderService::class);
		$statementBuilder = (new StatementBuilder())
			->orderBy('id ASC')
			->where('name = :name')
			->WithBindVariableValue('name', $orderName);
		$data = $orderService->getOrdersByStatement($statementBuilder->toStatement());
		if ($data->getResults() !== null)
		{
			foreach ($data->getResults() as $order) {
				$foo = array(
					"orderId"=>$order->getId(),
					"orderName"=>$order->getName()
				);
				array_push($output, $foo);
			}
		}
		return $output;
	}


	public function createOrder($orderName, $advertiserId, $traffickerId)
	{
		$output = [];
		$orderService = $this->dfpServices->get($this->session, OrderService::class);
		$order = new Order();
        $order->setName($orderName);
        $order->setAdvertiserId($advertiserId);
        //$order->setSalespersonId($traffickerId);
        $order->setTraffickerId($traffickerId);
        // Create the order on the server.
        $results = $orderService->createOrders([$order]);
        foreach ($results as $order) {
			$foo = array(
				"orderId"=>$order->getId(),
				"orderName"=>$order->getName()
			);
			array_push($output, $foo);
		}
		return $output;
	}
}