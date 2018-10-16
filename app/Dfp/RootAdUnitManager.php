<?php

namespace App\Dfp;

require __DIR__.'/../../vendor/autoload.php';

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
