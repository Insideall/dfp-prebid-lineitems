<?php

require (__DIR__."/../scriptLoader.php");


use App\Scripts\HeaderBiddingScript;

$entry = array(
	"ssp" => ['smartadserver'], // Needs to be bidder code defined in prebid documentation, ie appnexus, rubicon, improvedigital, smartadserver
	"priceGranularity" => "dense", // can be 'low', 'med', 'high', 'auto','dense', 'test'
	"currency"=>"EUR",
	"sizes" => [[728,90],[1000,200],[1000,250],[1000,300],[1000,90],[120,600],[160,600],[300,100],[300,1000],[300,250],[300,50],[300,600],[320,100],[320,50],[336,280],[970,150],[970,250],[970,90]]
);

$script = new HeaderBiddingScript;

$script->createAdUnits($entry);

//Done: criteo, smartadserver,rubicon,  improve, 


		