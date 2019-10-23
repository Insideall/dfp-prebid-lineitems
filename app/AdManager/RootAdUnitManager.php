<?php

namespace App\AdManager;

require __DIR__.'/../../vendor/autoload.php';

use Google\AdsApi\AdManager\v201908\NetworkService;

class RootAdUnitManager extends Manager
{
	public function setRootAdUnit()
	{
		$networkService = $this->serviceFactory->createNetworkService($this->session);
		$rootAdUnitId = $networkService->getCurrentNetwork()
			->getEffectiveRootAdUnitId();

		return $rootAdUnitId;
	}
}
