<?php

require (__DIR__."/../scriptLoader.php");

use App\Scripts\HeaderBiddingScript;

$entry = array(
	"priceGranularity" => "low", // can be 'low', 'med', 'high', 'auto','dense', 'test'
	"currency"=>"EUR",
	"sizes" => [[300, 250], [728, 90], [976, 91], [468, 60]]
);

$script = new HeaderBiddingScript;

$script->createGlobalAdUnits($entry);


