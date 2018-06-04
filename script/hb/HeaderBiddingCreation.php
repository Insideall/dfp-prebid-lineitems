<?php

require (__DIR__."/../scriptLoader.php");

use App\Scripts\HeaderBiddingScript;

$entry = array(
	"ssp" => ['rubicon'],
	"priceGranularity" => "dense",
	"currency"=>"EUR",
	"sizes" => [[300, 250], [728, 90], [976, 91], [468, 60]]
);

$script = new HeaderBiddingScript;

$script->createAdUnits($entry);


