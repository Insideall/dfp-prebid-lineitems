<?php

namespace App\AdManager;

use Google\AdsApi\AdManager\v201908\AdUnitTargeting;
use Google\AdsApi\AdManager\v201908\CostType;
use Google\AdsApi\AdManager\v201908\CreativePlaceholder;
use Google\AdsApi\AdManager\v201908\CreativeRotationType;
use Google\AdsApi\AdManager\v201908\CustomCriteria;
use Google\AdsApi\AdManager\v201908\CustomCriteriaComparisonOperator;
use Google\AdsApi\AdManager\v201908\CustomCriteriaSet;
use Google\AdsApi\AdManager\v201908\CustomCriteriaSetLogicalOperator;
use Google\AdsApi\AdManager\v201908\Goal;
use Google\AdsApi\AdManager\v201908\GoalType;
use Google\AdsApi\AdManager\v201908\InventoryTargeting;
use Google\AdsApi\AdManager\v201908\LineItem;
use Google\AdsApi\AdManager\v201908\LineItemService;
use Google\AdsApi\AdManager\v201908\LineItemType;
use Google\AdsApi\AdManager\v201908\Money;
use Google\AdsApi\AdManager\v201908\Size;
use Google\AdsApi\AdManager\v201908\StartDateTimeType;
use Google\AdsApi\AdManager\v201908\Targeting;
use Google\AdsApi\AdManager\Util\v201908\StatementBuilder;
use Google\AdsApi\AdManager\v201908\ApiException;

class LineItemManager extends Manager
{
	protected $orderId;
	protected $sizes;
	protected $ssp;
	protected $currency;
	protected $keyId;
	protected $valueId;
	protected $bucket;
	protected $lineItem;
	protected $lineItemName;
	protected $geoTargeting;


	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
		return $this;
	}

	public function setSizes($sizes)
	{
		$this->sizes = $sizes;
		return $this;
	}

	public function setSsp($ssp)
	{
		$this->ssp = $ssp;
		return $this;
	}

	public function setCurrency($currency)
	{
		$this->currency = $currency;
		return $this;
	}

	public function setKeyId($keyId)
	{
		$this->keyId = $keyId;
		return $this;
	}

	public function setValueId($valueId)
	{
		$this->valueId = $valueId;
		return $this;
	}

	public function setBucket($bucket)
	{
		$this->bucket = $bucket;
		return $this;
	}

	public function setGeoTargeting($geoTargeting)
	{
		$this->geoTargeting = $geoTargeting;
		return $this;
	}

	public function setRootAdUnitId($rootAdUnitId)
	{
		$this->rootAdUnitId = $rootAdUnitId;

		return $this;
	}

	public function setLineItemName()
	{
		if (empty($this->ssp)) {
			$this->lineItemName = 'Prebid_'.$this->bucket;
		} else {
			$this->lineItemName = ucfirst($this->ssp).'_Prebid_'.$this->bucket;
		}

		return $this;
	}

	public function setUpLineItem()
	{
		$lineItem = $this->getLineItem();
		if (empty($lineItem)) {
			return $this->createLineItem();
		} else {
			return $this->updateLineItem($lineItem);
		}
	}

	public function getAllLineItems()
	{
		$output = [];
		$lineItemService = $this->serviceFactory->createLineItemService($this->session);

		$statementBuilder = (new StatementBuilder())->orderBy('id ASC');
		$data = $lineItemService->getLineItemsByStatement($statementBuilder->toStatement());
		if (null == $data->getResults()) {
			return $output;
		}
		foreach ($data->getResults() as $lineItem) {
			array_push($output, $lineItem);
		}

		return $output;
	}

	public function getLineItem()
	{
		$output = '';
		$lineItemService = $this->serviceFactory->createLineItemService($this->session);
		$statementBuilder = (new StatementBuilder())
			->orderBy('id ASC')
			->where('name = :name AND orderId = :orderId')
			->WithBindVariableValue('name', $this->lineItemName)
			->WithBindVariableValue('orderId', $this->orderId);
		$data = $lineItemService->getLineItemsByStatement($statementBuilder->toStatement());
		if (null !== $data->getResults()) {
			foreach ($data->getResults() as $lineItem) {
				$output = $lineItem;
			}
		}

		return $output;
	}

	public function createLineItem()
	{
		$output = [];
		$lineItemService = $this->serviceFactory->createLineItemService($this->session);

		$attempts = 0;
		do {
			try {
				$results = $lineItemService->createLineItems([$this->setUpHeaderBiddingLineItem()
					->setStartDateTimeType(StartDateTimeType::IMMEDIATELY)
					->setUnlimitedEndDateTime(true),
				]);
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);

		foreach ($results as $i => $lineItem) {
			$foo = [
				'lineItemId' => $lineItem->getId(),
				'lineItemName' => $lineItem->getName(),
			];
			array_push($output, $foo);
		}

		return $output[0];
	}

	public function updateLineItem($lineItem)
	{
		$output = [];

		$lineItemService = $this->serviceFactory->createLineItemService($this->session);
		$attempts = 0;
		do {
			try {
				$results = $lineItemService->updateLineItems([$this->setUpHeaderBiddingLineItem()
					->setId($lineItem->getId())
					->setStartDateTime($lineItem->getStartDateTime())
					->setUnlimitedEndDateTime(true),
				]);
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);

		foreach ($results as $i => $lineItem) {
			$foo = [
				'lineItemId' => $lineItem->getId(),
				'lineItemName' => $lineItem->getName(),
			];
			array_push($output, $foo);
		}

		return $output[0];
	}

	private function setUpHeaderBiddingLineItem()
	{
		$lineItem = new LineItem();
		$lineItem->setName($this->lineItemName);
		$lineItem->setOrderId($this->orderId);

		$targeting = new Targeting();

		// Create inventory targeting.
		$inventoryTargeting = new InventoryTargeting();
		$adUnitTargeting = new AdUnitTargeting();
		$adUnitTargeting->setAdUnitId($this->rootAdUnitId);
		$adUnitTargeting->setIncludeDescendants(true);

		$inventoryTargeting->setTargetedAdUnits([$adUnitTargeting]);

		$targeting->setInventoryTargeting($inventoryTargeting);

		if($this->geoTargeting !== null){
			$targeting->setGeoTargeting($this->geoTargeting);
		}

		// Create Key/Values Targeting

		$customCriteria = new CustomCriteria();
		$customCriteria->setKeyId($this->keyId);
		$customCriteria->setOperator(CustomCriteriaComparisonOperator::IS);
		$customCriteria->setValueIds([$this->valueId]);

		$topCustomCriteriaSet = new CustomCriteriaSet();
		$topCustomCriteriaSet->setLogicalOperator(
			CustomCriteriaSetLogicalOperator::OR_VALUE
		);
		$topCustomCriteriaSet->setChildren(
			[$customCriteria]
		);
		$targeting->setCustomTargeting($topCustomCriteriaSet);

		$lineItem->setTargeting($targeting);

		// Allow the line item to be booked even if there is not enough inventory.
		$lineItem->setAllowOverbook(true);

		// Set the line item type to STANDARD and priority to High. In this case,
		// 8 would be Normal, and 10 would be Low.
		$lineItem->setLineItemType(LineItemType::PRICE_PRIORITY);
		$lineItem->setPriority(12);

		// Set the creative rotation type to even.
		$lineItem->setCreativeRotationType(CreativeRotationType::EVEN);

		// Set the size of creatives that can be associated with this line item.
		$lineItem->setCreativePlaceholders($this->setCreativePlaceholders());

		// Set the length of the line item to run.
		//$lineItem->setStartDateTimeType(StartDateTimeType::IMMEDIATELY);
		//$lineItem->setUnlimitedEndDateTime(true);

		// Set the cost per unit to $2.
		$lineItem->setCostType(CostType::CPM);
		$lineItem->setCostPerUnit(new Money($this->currency, floatval($this->bucket) * 1000000));

		$goal = new Goal();
		$goal->setGoalType(GoalType::NONE);
		$lineItem->setPrimaryGoal($goal);

		return $lineItem;
	}

	private function setCreativePlaceholders()
	{
		$output = [];
		foreach ($this->sizes as $element) {
			$size = new Size();
			$size->setWidth($element[0]);
			$size->setHeight($element[1]);
			$size->setIsAspectRatio(false);

			// Create the creative placeholder.
			$creativePlaceholder = new CreativePlaceholder();
			$creativePlaceholder->setSize($size);
			array_push($output, $creativePlaceholder);
		}

		return $output;
	}
}
