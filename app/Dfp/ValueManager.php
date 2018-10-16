<?php

namespace App\Dfp;

require(__DIR__."/../../vendor/autoload.php");

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\v201802\CustomTargetingKey;
use Google\AdsApi\Dfp\v201802\CustomTargetingKeyType;
use Google\AdsApi\Dfp\v201802\CustomTargetingService;
use Google\AdsApi\Dfp\v201802\CustomTargetingValue;
use Google\AdsApi\Dfp\v201802\CustomTargetingValueMatchType;
use Google\AdsApi\Dfp\Util\v201802\StatementBuilder;

class ValueManager extends DfpManager
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
		$existing = $this->getExistingValuesFromDFP();

		//We create a table with only existing keys
		$existingValuesList = [];
		$output = [];
		
		foreach ($existing as $foo) {
			array_push($existingValuesList, $foo['valueName']);
			if (in_array($foo['valueName'], $valuesList)) {
				array_push($output, $foo);
			}
		}

		//We create a list with values to be created
		$valuesToBeCreated = [];
		foreach ($valuesList as $element) {
			if(!in_array($element, $existingValuesList))
			{
				array_push($valuesToBeCreated, $element);
			}
		}
		if(!empty($valuesToBeCreated))
		{
			$foo = $this->createCustomTargetingValues($valuesToBeCreated);
			foreach($foo as $bar)
			{
				array_push($output, $bar);
			}
		}
		return $output;
	}

	public function createCustomTargetingValues($valuesToBeCreated)
	{
		if(!is_array($valuesToBeCreated))
		{
			echo "The input needs to be an array";
			exit;
		}

		$customTargetingService = $this->dfpServices->get($this->session, CustomTargetingService::class);
		$output = [];
		$values = [];
		foreach ($valuesToBeCreated as $value)
		{	
			$foo= new CustomTargetingValue();
	        $foo->setCustomTargetingKeyId($this->keyId);
	        $foo->setDisplayName($value);
	        $foo->setName($value);
	        $foo->setMatchType(CustomTargetingValueMatchType::EXACT);
    		array_push($values,$foo);
    	}
    	$values = $customTargetingService->createCustomTargetingValues($values);
    	foreach ($values as $value) {
            $foo = array(
                "valueId" => $value->getId(),
                "valueName"=>$value->getName(),
                "valueDisplayName"=>$value->getDisplayName()
            );
            printf(
            	"A custom targeting value with ID %d, belonging to key with ID %d, "
                . "name '%s', and display name '%s' was created.\n",
                $value->getId(),
                $value->getCustomTargetingKeyId(),
                $value->getName(),
                $value->getDisplayName()
            );
            array_push($output, $foo);
        }
        return $output;
	}


	public function getExistingValuesFromDFP()
	{	
		$output = [];
		$pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
		$customTargetingService = $this->dfpServices->get($this->session, CustomTargetingService::class);
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
			if ($data->getResults() !== null)
			{
				$totalResultSetSize = $data->getTotalResultSetSize();
				foreach ($data->getResults() as $value) {
				    $foo = array(
				  		"valueId" => $value->getId(),
				        "valueName" => $value->getName(),
				        "valueDisplayName" => $value->getDisplayName()
				    );
				    array_push($output, $foo);
				}
			    $statementBuilder->increaseOffsetBy($pageSize);
			}
		} while ($statementBuilder->getOffset() < $totalResultSetSize);
		return $output;
	}

}