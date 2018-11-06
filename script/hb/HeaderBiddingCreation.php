<?php

require __DIR__.'/../scriptLoader.php';

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

$entry = [
	'ssp' => ['ix'], // Needs to be bidder code defined in prebid documentation, ie appnexus, rubicon, improvedigital, smartadserver
	'priceGranularity' => 'dense', // can be 'low', 'med', 'high', 'auto','dense', 'test'
	'currency' => 'EUR',
	'sizes' => [[970,250],[970,90],[728,250],[728,90],[320,50],[300,100],[250,250],[300,250],[300,600]]
];

$script = new HeaderBiddingScript();

$script->createAdUnits($entry);

//Done: criteo, smartadserver,rubicon,  improve,
