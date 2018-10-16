<?php

namespace App\Scripts;

class HeaderBiddingScript
{
	protected $traffikerId;
	protected $advertiserId;
	protected $orderId;
	protected $keyId;


	static function createAdUnits($params)
	{
		foreach($params['ssp'] as $ssp)
		{
			$param = array(
				"orderName" => "Insideall - Prebid - ".ucfirst($ssp),
				"advertiserName" => "Insideall - Prebid - ".ucfirst($ssp),
				"priceGranularity" => $params["priceGranularity"],
				"sizes" =>$params["sizes"],
				"priceKeyName"=>substr("hb_pb_$ssp",0,20),
				"adidKeyName"=>substr("hb_adid_$ssp",0,20),
				"sizeKeyName"=>substr("hb_size_$ssp",0,20),
				"currency"=>$params['currency'],
				"ssp"=>$ssp
			);
			$script = new SSPScript($param);

			$script->createAdUnits();
		}
	}

	static function createGlobalAdunits($params)
	{
		$params = array(
			"orderName" => "Insideall - Prebid",
			"advertiserName" => "Insideall - Prebid",
			"priceGranularity" => $params["priceGranularity"],
			"sizes" =>$params["sizes"],
			"priceKeyName"=>substr("hb_pb",0,20),
			"adidKeyName"=>substr("hb_adid",0,20),
			"sizeKeyName"=>substr("hb_size",0,20),
			"currency"=>$params['currency'],
			"ssp"=>""
		);
		$script = new SSPScript($params);

		$script->createAdUnits();

	}
}