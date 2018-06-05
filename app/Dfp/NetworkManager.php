<?php

namespace App\Dfp;

require(__DIR__."/../../vendor/autoload.php");

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\v201802\NetworkService;

class NetworkManager extends DfpManager
{
	
	protected $dfpServices;
	protected $session;

	public function getCurrentNetwork()
	{
		$networkService  = $this->dfpServices->get($this->session, NetworkService::class);

		$network = $networkService->getCurrentNetwork();
		
		$output = array(
	  		"networkCode" => $network->getNetworkCode(),
	        "networkName" => $network->getDisplayName()
	    );
	
		return $output;
	}

	public function makeTestNetwork()
	{
		$networkService  = $this->dfpServices->get($this->session, NetworkService::class);
		$network = $networkService->makeTestNetwork();

		printf(
            "Test network with network code '%s' and display name '%s' created.\n"
            . 'You may now sign in at' . " https://www.google.com/dfp/main?networkCode=%s\n",
            $network->getNetworkCode(),
            $network->getDisplayName(),
            $network->getNetworkCode()
        );

	}

}


