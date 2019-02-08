<?php

namespace App\AdManager;

require __DIR__.'/../../vendor/autoload.php';

class NetworkManager extends Manager
{
	protected $dfpServices;
	protected $session;

	public function getCurrentNetwork()
	{
		$networkService = $this->serviceFactory->createNetworkService($this->session);

		$network = $networkService->getCurrentNetwork();

		$output = [
			'networkCode' => $network->getNetworkCode(),
			'networkName' => $network->getDisplayName(),
		];

		return $output;
	}

	public function makeTestNetwork()
	{
		$networkService = $this->serviceFactory->createNetworkService($this->session);
		$network = $networkService->makeTestNetwork();

		printf(
			"Test network with network code '%s' and display name '%s' created.\n"
			.'You may now sign in at'." https://www.google.com/dfp/main?networkCode=%s\n",
			$network->getNetworkCode(),
			$network->getDisplayName(),
			$network->getNetworkCode()
		);
	}
}
