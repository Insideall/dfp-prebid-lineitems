<?php

putenv('HOME='.dirname(__DIR__)."/../");
require __DIR__.'/../../vendor/autoload.php';

use App\Scripts\HeaderBiddingScript;



/*
$buckets = 
	["buckets" =>[
		['precision' => 2, 'min' => 0, 'max' => 120, 'increment' => 0.40],
		['precision' => 2, 'min' => 120, 'max' => 320, 'increment' => 2],
		['precision' => 2, 'min' => 320, 'max' => 800, 'increment' => 20]
	]
]; 
*/

// $entry set import
require __DIR__.'/../../customerConfig/Bayard.php';


/*
$entry = [
	'ssp' => ['appnexus'], // Needs to be bidder code defined in prebid documentation, ie appnexus, rubicon, improvedigital, smartadserver
	'priceGranularity' => 'dense', // can be 'low', 'med', 'high', 'auto','dense', 'test'
	'currency' => 'EUR',
	'sizes' => [[120,600],[160,600],[300,50],[300,100],[300,250],[300,600],[300,1000],[320,50],[320,100],[336,280],[728,90],[970,90],[970,150],[970,250],[1000,90],[1000,200],[1000,250],[1000,300]],
	'orderPrefix' => 'Insideall - Prebid - '
];
*/

$script = new HeaderBiddingScript();

$script->createAdUnits($entry);

