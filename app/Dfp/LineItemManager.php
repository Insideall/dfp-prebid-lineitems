<?php

namespace App\Dfp;

require(__DIR__."/../../vendor/autoload.php");

use DateTime;
use DateTimeZone;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\Util\v201802\DfpDateTimes;
use Google\AdsApi\Dfp\v201802\AdUnitTargeting;
use Google\AdsApi\Dfp\v201802\CostType;
use Google\AdsApi\Dfp\v201802\CreativePlaceholder;
use Google\AdsApi\Dfp\v201802\CreativeRotationType;
use Google\AdsApi\Dfp\v201802\CustomCriteria;
use Google\AdsApi\Dfp\v201802\CustomCriteriaComparisonOperator;
use Google\AdsApi\Dfp\v201802\CustomCriteriaSet;
use Google\AdsApi\Dfp\v201802\CustomCriteriaSetLogicalOperator;
use Google\AdsApi\Dfp\v201802\Goal;
use Google\AdsApi\Dfp\v201802\GoalType;
use Google\AdsApi\Dfp\v201802\InventoryTargeting;
use Google\AdsApi\Dfp\v201802\LineItem;
use Google\AdsApi\Dfp\v201802\LineItemService;
use Google\AdsApi\Dfp\v201802\LineItemType;
use Google\AdsApi\Dfp\v201802\Money;
use Google\AdsApi\Dfp\v201802\NetworkService;
use Google\AdsApi\Dfp\v201802\Size;
use Google\AdsApi\Dfp\v201802\StartDateTimeType;
use Google\AdsApi\Dfp\v201802\Targeting;
use Google\AdsApi\Dfp\v201802\UnitType;
use Google\AdsApi\Dfp\Util\v201802\StatementBuilder;


class LineItemManager extends DfpManager
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

    public function setRootAdUnitId($rootAdUnitId)
    {
        $this->rootAdUnitId = $rootAdUnitId;
        return $this;
    }

    public function setLineItemName()
    {
        if (empty($this->ssp)){
            $this->lineItemName = "Prebid_".$this->bucket;
        } else {
            $this->lineItemName = ucfirst($this->ssp)."_Prebid_".$this->bucket;
        }
        return $this;
    }

    public function setUpLineItem()
    {    
        $lineItem = $this->getLineItem();
        if(empty($lineItem))
        {
            return $this->createLineItem();
        }
        else
        {
            return $this->updateLineItem($lineItem);
        }
    }

    public function getAllLineItems()
	{
		$output = [];
		$lineItemService = $this->dfpServices->get($this->session, LineItemService::class);

		$statementBuilder = (new StatementBuilder())->orderBy('id ASC');
		$data = $lineItemService->getLineItemsByStatement($statementBuilder->toStatement());
		if($data->getResults() == null)
		{
			return $output;
		}
		foreach ($data->getResults() as $lineItem) {
		    array_push($output, $lineItem);
		}
		return $output;
	}

    public function getLineItem()
    {
        $output = "";
        $lineItemService = $this->dfpServices->get($this->session, LineItemService::class);
        $statementBuilder = (new StatementBuilder())
            ->orderBy('id ASC')
            ->where('name = :name AND orderId = :orderId')
            ->WithBindVariableValue('name', $this->lineItemName)
            ->WithBindVariableValue('orderId', $this->orderId);
        $data = $lineItemService->getLineItemsByStatement($statementBuilder->toStatement());
        if ($data->getResults() !== null)
        {
            foreach ($data->getResults() as $lineItem) {
                $output = $lineItem;
            }
        }
        return $output;
    }

	public function createLineItem()
	{
		$output = [];
		$lineItemService = $this->dfpServices->get($this->session, LineItemService::class);
        
        $results = $lineItemService->createLineItems([$this->setUpHeaderBiddingLineItem()
            ->setStartDateTimeType(StartDateTimeType::IMMEDIATELY)
            ->setUnlimitedEndDateTime(true)
        ]);

        foreach ($results as $i => $lineItem) {
            $foo = array(
                "lineItemId"=>$lineItem->getId(),
                "lineItemName"=>$lineItem->getName()
            );
            array_push($output, $foo);
        }
        return $output[0];
	}

    public function updateLineItem($lineItem)
    {
        $output = [];

        $lineItemService = $this->dfpServices->get($this->session, LineItemService::class);
        $results = $lineItemService->updateLineItems([$this->setUpHeaderBiddingLineItem()
            ->setId($lineItem->getId())
            ->setStartDateTime($lineItem->getStartDateTime())
            ->setUnlimitedEndDateTime(true)
        ]);
        
        foreach ($results as $i => $lineItem) {
            $foo = array(
                "lineItemId"=>$lineItem->getId(),
                "lineItemName"=>$lineItem->getName()
            );
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
        $lineItem->setCostPerUnit(new Money($this->currency, floatval($this->bucket)*1000000));

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