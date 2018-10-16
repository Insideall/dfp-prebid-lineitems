<?php

namespace App\Dfp;

require __DIR__.'/../../vendor/autoload.php';

use Google\AdsApi\Dfp\v201802\CustomTargetingKey;
use Google\AdsApi\Dfp\v201802\CustomTargetingKeyType;
use Google\AdsApi\Dfp\v201802\CustomTargetingService;
use Google\AdsApi\Dfp\Util\v201802\StatementBuilder;

class KeyManager extends DfpManager
{
	public function setUpCustomTargetingKey($keyName)
	{
		if (empty(($foo = $this->getCustomTargetingKey($keyName)))) {
			$foo = $this->createCustomTargetingKey($keyName);
		}

		return $foo[0]['keyId'];
	}

	public function createCustomTargetingKey($keyName)
	{
		$output = [];
		$customTargetingService = $this->dfpServices->get($this->session, CustomTargetingService::class);
		$key = new CustomTargetingKey();
		$key->setDisplayName($keyName);
		$key->setName($keyName);
		$key->setType(CustomTargetingKeyType::FREEFORM);

		$keys = $customTargetingService->createCustomTargetingKeys([$key]);
		foreach ($keys as $key) {
			$foo = [
				'keyId' => $key->getId(),
				'keyName' => $key->getName(),
				'keyDisplayNameId' => $key->getDisplayName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	public function getAllCustomTargetingKeys()
	{
		$output = [];
		$customTargetingService = $this->dfpServices->get($this->session, CustomTargetingService::class);
		$statementBuilder = (new StatementBuilder())->orderBy('id ASC');
		$data = $customTargetingService->getCustomTargetingKeysByStatement($statementBuilder->toStatement());
		if (null == $data->getResults()) {
			return $output;
		}
		foreach ($data->getResults() as $key) {
			$foo = [
				'keyId' => $key->getId(),
				'keyName' => $key->getName(),
				'keyDisplayNameId' => $key->getDisplayName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	public function getCustomTargetingKey($keyName)
	{
		$output = [];
		$customTargetingService = $this->dfpServices->get($this->session, CustomTargetingService::class);
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
					'keyDisplayNameId' => $key->getDisplayName(),
				];
				array_push($output, $foo);
			}
		}

		return $output;
	}
}
