<?php

namespace App\AdManager;

require __DIR__.'/../../vendor/autoload.php';

use Google\AdsApi\AdManager\v201811\ActivateCustomTargetingKeys;
use Google\AdsApi\AdManager\v201811\CustomTargetingKey;
use Google\AdsApi\AdManager\v201811\CustomTargetingKeyType;
use Google\AdsApi\AdManager\Util\v201811\StatementBuilder;


class KeyManager extends Manager
{
	public function setUpCustomTargetingKey($keyName)
	{
		if (empty(($foo = $this->getCustomTargetingKey($keyName)))) {
			$foo = $this->createCustomTargetingKey($keyName);
		} elseif ($foo[0]['keyStatus'] === 'INACTIVE') {
			$this->activateCustomTargetingKey($keyName);
		}

		return $foo[0]['keyId'];
	}

	public function createCustomTargetingKey($keyName)
	{
		$output = [];
		$customTargetingService = $this->serviceFactory->createCustomTargetingService($this->session);
		$key = new CustomTargetingKey();
		$key->setDisplayName($keyName);
		$key->setName($keyName);
		$key->setType(CustomTargetingKeyType::FREEFORM);

		$keys = $customTargetingService->createCustomTargetingKeys([$key]);
		foreach ($keys as $key) {
			$foo = [
				'keyId' => $key->getId(),
				'keyName' => $key->getName(),
				'keyStatus' => $key->getStatus(),
				'keyDisplayNameId' => $key->getDisplayName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	public function getAllCustomTargetingKeys()
	{
		$output = [];
		$customTargetingService = $this->serviceFactory->createCustomTargetingService($this->session);
		$statementBuilder = (new StatementBuilder())->orderBy('id ASC');
		$data = $customTargetingService->getCustomTargetingKeysByStatement($statementBuilder->toStatement());
		if (null == $data->getResults()) {
			return $output;
		}
		foreach ($data->getResults() as $key) {
			$foo = [
				'keyId' => $key->getId(),
				'keyName' => $key->getName(),
				'keyStatus' => $key->getStatus(),
				'keyDisplayNameId' => $key->getDisplayName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	public function activateCustomTargetingKey($keyName)
	{
		$action = new ActivateCustomTargetingKeys();

		$statementBuilder = (new StatementBuilder())
			->where('name = :name')
			->WithBindVariableValue('name', $keyName);

		$customTargetingService = $this->serviceFactory->createCustomTargetingService($this->session);
		$result = $customTargetingService->performCustomTargetingKeyAction($action, $statementBuilder->toStatement());
	}

	public function getCustomTargetingKey($keyName)
	{
		$output = [];
		$customTargetingService = $this->serviceFactory->createCustomTargetingService($this->session);
		$statementBuilder = (new StatementBuilder())
			->orderBy('id ASC')
			->where('name = :name')
			->WithBindVariableValue('name', $keyName);
		$data = $customTargetingService->getCustomTargetingKeysByStatement($statementBuilder->toStatement());
		if (null !== $data->getResults()) {
			foreach ($data->getResults() as $key) {
				$foo = [
					'keyId' => $key->getId(),
					'keyName' => $key->getName(),
					'keyStatus' => $key->getStatus(),
					'keyDisplayNameId' => $key->getDisplayName(),
				];
				array_push($output, $foo);
			}
		}

		return $output;
	}
}
