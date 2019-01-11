<?php

namespace App\Scripts;

class SSPScript extends \App\AdManager\Manager
{
	protected $orderName;
	protected $advertiserName;
	protected $priceGranularity;
	protected $sizes;
	protected $priceKeyName;
	protected $adidKeyName;
	protected $sizeKeyName;
	protected $ssp;
	protected $currency;
	protected $licasUpdate;

	protected $traffickerId;
	protected $advertiserId;
	protected $orderId;
	protected $priceKeyId;
	protected $adidKeyId;
	protected $sizeKeyId;
	protected $valuesList;
	protected $dfpValuesList;
	protected $creativesList;
	protected $rootAdUnitId;

	public function __construct($params)
	{
		foreach ($params as $key => $value) {
			$this->$key = $value;
		}
	}

	public function createAdUnits()
	{
		$this->valuesList = Buckets::createBuckets($this->priceGranularity);

		//Get the Trafficker Id
		$this->traffickerId = (new \App\AdManager\UserManager())->getUserId();
		echo 'TraffickerId: '.$this->traffickerId."\n";

		

		//Get the Advertising Company Id
		$this->advertiserId = (new \App\AdManager\CompanyManager())->setUpCompany($this->advertiserName);
		echo 'AdvertiserName : '.$this->advertiserName."\tAdvertiserId: ".$this->advertiserId."\n";

		//Get the OrderId
		$this->orderId = (new \App\AdManager\OrderManager())->setUpOrder($this->orderName, $this->advertiserId, $this->traffickerId);
		echo 'OrderName : '.$this->orderName."\tOrderId: ".$this->orderId."\n";


		//Create and get KeyIds
		$this->priceKeyId = (new \App\AdManager\KeyManager())->setUpCustomTargetingKey($this->priceKeyName);
		echo 'PriceKeyName : '.$this->priceKeyName."\tPriceKeyId: ".$this->priceKeyId."\n";
		$this->adidKeyId = (new \App\AdManager\KeyManager())->setUpCustomTargetingKey($this->adidKeyName);
		echo 'AdidKeyName : '.$this->adidKeyName."\tAdidKeyId: ".$this->adidKeyId."\n";
		$this->sizeKeyId = (new \App\AdManager\KeyManager())->setUpCustomTargetingKey($this->sizeKeyName);
		echo 'SizeKeyName : '.$this->sizeKeyName."\tSizeKeyId: ".$this->sizeKeyId."\n";

		//Create and get Values
		$valuesManager = new \App\AdManager\ValueManager();
		$valuesManager->setKeyId($this->priceKeyId);
		$this->dfpValuesList = $valuesManager->convertValuesListToDFPValuesList($this->valuesList);
		echo "Values List Created\n";




		$creativeManager = new \App\AdManager\CreativeManager();
		$creativeManager->setSsp($this->ssp)
			->setAdvertiserId($this->advertiserId);
		$this->creativesList = $creativeManager->setUpCreatives();


		echo "\n\n".json_encode($this->creativesList)."\n\n";
		$this->rootAdUnitId = (new \App\AdManager\RootAdUnitManager())->setRootAdUnit();
		echo 'rootAdUnitId: '.$this->rootAdUnitId."\n";

		$i = 0;

		foreach ($this->dfpValuesList as $dfpValue) {
			$lineItemManager = new \App\AdManager\LineItemManager();
			$lineItemManager->setOrderId($this->orderId)
				->setSizes($this->sizes)
				->setSsp($this->ssp)
				->setCurrency($this->currency)
				->setKeyId($this->priceKeyId)
				->setValueId($dfpValue['valueId'])
				->setBucket($dfpValue['valueName'])
				->setRootAdUnitId($this->rootAdUnitId)
				->setLineItemName();
			$lineItem = $lineItemManager->setUpLineItem();
			$licaManager = new \App\AdManager\LineItemCreativeAssociationManager();
			$licaManager->setLineItem($lineItem)
				->setCreativeList($this->creativesList)
				->setSizeOverride($this->sizes)
				->setUpLica();

			++$i;
			if (empty($this->ssp)) {
				echo "\n\nLine Item Prebid_".$dfpValue['valueName']." created/updated.\n";
			} else {
				echo "\n\nLine Item ".ucfirst($this->ssp).'_Prebid_'.$dfpValue['valueName']." created/updated.\n";
			}

			echo round(($i / count($this->dfpValuesList)) * 100, 1)."% done\n\n";
		}

		(new \App\AdManager\OrderManager())->approveOrder($this->orderId);
	}
}
