<?php

namespace App\Dfp;

require("../../vendor/autoload.php");

use DateTime;
use DateTimeZone;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;

use Google\AdsApi\Dfp\v201802\NetworkService;

class RootAdUnitManager extends DfpManager
{
	public function setRootAdUnit()
	{
		$networkService = $this->dfpServices->get($this->session, NetworkService::class);
        $rootAdUnitId = $networkService->getCurrentNetwork()
            ->getEffectiveRootAdUnitId();
        return $rootAdUnitId; 
	}
}