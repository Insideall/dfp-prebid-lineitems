<?php

namespace App\Scripts;

class HeaderBiddingScript
{
	protected $traffikerId;
	protected $advertiserId;
	protected $orderId;
	protected $keyId;
	protected $adsApi;

	public function setCredentials($credentials)
	{
		$this->adsApi = new \App\Scripts\AdsApiGenerator;
		$this->adsApi->setCredentials($credentials)
			->generateAdsApi();
		return $this;
	}

	public function clearCredentials()
	{
		
		$this->adsApi->deleteAdsApi();
	}

	public function createAdUnits($params)
	{
		foreach ($params['ssp'] as $ssp) {
			$param = [
				'orderName' => $params['orderPrefix'].ucfirst($ssp),
				'advertiserName' => $params['orderPrefix'].ucfirst($ssp),
				'priceGranularity' => $params['priceGranularity'],
				'sizes' => $params['sizes'],
				'priceKeyName' => substr("hb_pb_$ssp", 0, 20),
				'adidKeyName' => substr("hb_adid_$ssp", 0, 20),
				'sizeKeyName' => substr("hb_size_$ssp", 0, 20),
				'currency' => $params['currency'],
				'ssp' => $ssp
			];
			if(isset($params['geoTargetingList'])){
				$param['geoTargetingList'] = $params['geoTargetingList'];
			}
			$script = new SSPScript($param);

			$script->createAdUnits();
		}
		return $this;
	}

	public function updateCreatives($params, $type)
	{
		foreach ($params['ssp'] as $ssp) {
			$param = [
				'orderName' => $params['orderPrefix'].ucfirst($ssp),
				'advertiserName' => $params['orderPrefix'].ucfirst($ssp),
				'priceGranularity' => $params['priceGranularity'],
				'sizes' => $params['sizes'],
				'priceKeyName' => substr("hb_pb_$ssp", 0, 20),
				'adidKeyName' => substr("hb_adid_$ssp", 0, 20),
				'sizeKeyName' => substr("hb_size_$ssp", 0, 20),
				'currency' => $params['currency'],
				'ssp' => $ssp,
			];
			$script = new SSPScript($param);

			$script->updateCreatives($type);
		}
		return $this;
	}

	public function createGlobalAdunits($params)
	{
		$params = [
			'orderName' => $params['orderPrefix'],
			'advertiserName' => $params['orderPrefix'],
			'priceGranularity' => $params['priceGranularity'],
			'sizes' => $params['sizes'],
			'priceKeyName' => substr('hb_pb', 0, 20),
			'adidKeyName' => substr('hb_adid', 0, 20),
			'sizeKeyName' => substr('hb_size', 0, 20),
			'currency' => $params['currency'],
			'ssp' => '',
		];
		$script = new SSPScript($params);

		$script->createAdUnits();
	}
}
