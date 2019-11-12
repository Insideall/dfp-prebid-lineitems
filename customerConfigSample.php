<?php

$networkCode = 21700827184;

$entry = [
	'ssp' => ['appnexus'], // Needs to be bidder code defined in prebid documentation, ie appnexus, rubicon, improvedigital, smartadserver
	'priceGranularity' => 'dense', // can be 'low', 'med', 'high', 'auto','dense', 'test'
	'currency' => 'EUR',
	'sizes' => [[120,600],[160,600],[300,50],[300,100],[300,250],[300,600],[300,1000],[320,50],[320,100],[336,280],[728,90],[970,90],[970,150],[970,250],[1000,90],[1000,200],[1000,250],[1000,300]],
	'orderPrefix' => 'Insideall - Prebid - ',
	//'geoTargetingList' => "dz, pk, ke, pt", //Geo Targeting is not mandatory, if not mentionned, the setup will apply to all Geographies - You need to stick to this format
	/*'customTargeting' => [
		'country' => 'dz, pk, ke, pt, kr, ru, es, ch, fr, sk, cl, qa, hu, de, pe, be, pl, ec, hk, co, br, mx, cz, uy, do, ua, sa, at, se, uk, au, dk, ma, ar, kw, ve, it, th, ng, gt, tr, us, my, ro, za, ca, jp, ae, ph, in, nl, id, ao, nz, tw, cn, sg, ie'
	] Add a key value targeting, not mandatory*/
];

