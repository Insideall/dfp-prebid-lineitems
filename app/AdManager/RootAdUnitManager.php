<?php

namespace App\AdManager;

require __DIR__.'/../../vendor/autoload.php';

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
