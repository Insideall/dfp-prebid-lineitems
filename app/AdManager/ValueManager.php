<?php

namespace App\AdManager;

require __DIR__.'/../../vendor/autoload.php';


use Google\AdsApi\AdManager\v201811\ActivateCustomTargetingValues;
use Google\AdsApi\AdManager\v201811\CustomTargetingValue;
use Google\AdsApi\AdManager\v201811\CustomTargetingValueMatchType;
use Google\AdsApi\AdManager\v201811\Statement;
use Google\AdsApi\AdManager\Util\v201811\StatementBuilder;

class ValueManager extends Manager
{
	protected $keyId;
	protected $existingDFPValues;

	public function setKeyId($keyId)
	{
		$this->keyId = $keyId;

		return $this;
	}

	public function getExistingValues()
	{
		return $this->existingValues;
	}

	public function convertValuesListToDFPValuesList($valuesList)
	{
		//We get from DFP which keys already exists
		$existing = $this->getExistingValuesFromAdManager();

		//We create a table with only existing keys
		$existingValuesList = [];
		$inactiveValuesList = [];
		$output = [];

		foreach ($existing as $foo) {
			array_push($existingValuesList, $foo['valueName']);
			if (in_array($foo['valueName'], $valuesList)) {
				array_push($output, $foo);
				if ($foo['valueStatus'] === 'INACTIVE') {
					$inactiveValuesList[$foo['valueName']] = $foo['valueId'];
				}
			}
		}

		//We create a list with values to be created
		$valuesToBeCreated = [];
		$valuesToBeActivated = [];
		foreach ($valuesList as $element) {
			if (!in_array($element, $existingValuesList)) {
				array_push($valuesToBeCreated, $element);
			} else if (isset($inactiveValuesList[$element])) {
				array_push($valuesToBeActivated, $inactiveValuesList[$element]);
			}
		}

		if (!empty($valuesToBeCreated)) {
			$foo = $this->createCustomTargetingValues($valuesToBeCreated);
			foreach ($foo as $bar) {
				array_push($output, $bar);
			}
		}

		if (!empty($valuesToBeActivated)) {
			$this->activateCustomTargetingValues($valuesToBeActivated);
		}

		return $output;
	}

	public function createCustomTargetingValues($valuesToBeCreated)
	{
		if (!is_array($valuesToBeCreated)) {
			echo 'The input needs to be an array';
			exit;
		}

		$customTargetingService = $this->serviceFactory->createCustomTargetingService($this->session);
		$output = [];
		$values = [];
		foreach ($valuesToBeCreated as $value) {
			$foo = new CustomTargetingValue();
			$foo->setCustomTargetingKeyId($this->keyId);
			$foo->setDisplayName($value);
			$foo->setName($value);
			$foo->setMatchType(CustomTargetingValueMatchType::EXACT);
			array_push($values, $foo);
		}
		$values = $customTargetingService->createCustomTargetingValues($values);
		foreach ($values as $value) {
			$foo = [
				'valueId' => $value->getId(),
				'valueName' => $value->getName(),
				'valueDisplayName' => $value->getDisplayName(),
			];
			printf(
				'A custom targeting value with ID %d, belonging to key with ID %d, '
				."name '%s', and display name '%s' was created.\n",
				$value->getId(),
				$value->getCustomTargetingKeyId(),
				$value->getName(),
				$value->getDisplayName()
			);
			array_push($output, $foo);
		}

		return $output;
	}

	public function activateCustomTargetingValues($valuesToBeActivated)
	{
		$action = new ActivateCustomTargetingValues();

		$statementText = sprintf("WHERE id IN (%s)", implode(',', $valuesToBeActivated));
		$statement = new Statement($statementText);

		$customTargetingService = $this->serviceFactory->createCustomTargetingService($this->session);
		$result = $customTargetingService->performCustomTargetingValueAction($action, $statement);
		if ($result->getNumChanges() !== count($valuesToBeActivated)) {
			throw new \Exception("Failed to activate key values");
		}
	}

	public function getExistingValuesFromAdManager()
	{
		$output = [];
		$pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
		$customTargetingService = $this->serviceFactory->createCustomTargetingService($this->session);
		$statementBuilder = (new StatementBuilder())->where('customTargetingKeyId = :customTargetingKeyId')
			->orderBy('id ASC')
			->limit($pageSize);
		$statementBuilder->withBindVariableValue(
			'customTargetingKeyId',
			$this->keyId
		);
		$totalResultSetSize = 0;
		do {
			$data = $customTargetingService->getCustomTargetingValuesByStatement($statementBuilder->toStatement());
			if (null !== $data->getResults()) {
				$totalResultSetSize = $data->getTotalResultSetSize();
				foreach ($data->getResults() as $value) {
					$foo = [
						'valueId' => $value->getId(),
						'valueName' => $value->getName(),
						'valueStatus' => $value->getStatus(),
						'valueDisplayName' => $value->getDisplayName(),
					];
					array_push($output, $foo);
				}
				$statementBuilder->increaseOffsetBy($pageSize);
			}
		} while ($statementBuilder->getOffset() < $totalResultSetSize);

		return $output;
	}
}
