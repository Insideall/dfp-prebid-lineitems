<?php

namespace App\AdManager;

use Google\AdsApi\AdManager\Util\v201908\StatementBuilder;
use Google\AdsApi\AdManager\Util\v201908\Pql;
use Google\AdsApi\AdManager\v201908\Targeting;
use Google\AdsApi\AdManager\v201908\GeoTargeting;
use Google\AdsApi\AdManager\v201908\Location;

class GeoTargetingManager extends Manager
{
	public function setGeoTargeting($targeting)
	{
		$targeting = str_replace(" ", "", $targeting);
		$targeting = explode(",", $targeting);
		$locations = [];
		$targetingList = $this->getGeoTargetingList();

		foreach ($targeting as $target) {
			$bar = array_search(strtoupper($target), array_column($targetingList, "countrycode"));
			if($bar !== false){
				$foo = $targetingList[$bar];
				$location = new Location;
				$location->setId($foo["id"]);
				array_push($locations, $location);
			} else {
				//die("\n\n$target does not exist\n");
			}
		}
		$geoTargeting = new GeoTargeting();
		$geoTargeting->setTargetedLocations($locations);
		return $geoTargeting;

	}



	public function getGeoTargetingList()
	{
		$type = 'Country';

		$pqlService = $this->serviceFactory->createPublisherQueryLanguageService( $this->session);
		$statementBuilder = new StatementBuilder();
        $statementBuilder->select(
            'Id, Name, CanonicalParentId, ParentIds, CountryCode'
        );
        $statementBuilder->from('Geo_Target');
        $statementBuilder->where(
            'Type = :type and Targetable = true'
        );
        $statementBuilder->orderBy('CountryCode ASC, Name ASC');
        $statementBuilder->offset(0);
        $statementBuilder->limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);
        $statementBuilder->withBindVariableValue('type', $type);
        //$statementBuilder->withBindVariableValue('countryCode', $countryCode);
        $combinedResultSet = null;
        $i = 0;
        do {
            // Get all cities.
            $resultSet = $pqlService->select($statementBuilder->toStatement());
            
            // Combine result sets with previous ones.
            $combinedResultSet = is_null($combinedResultSet)
                ? $resultSet
                : Pql::combineResultSets(
                    $combinedResultSet,
                    $resultSet
                );
            $rows = $resultSet->getRows();

            
            printf(
                "%d) %d geo targets beginning at offset %d were found.%s",
                $i++,
                is_null($rows) ? 0 : count($rows),
                $statementBuilder->getOffset(),
                PHP_EOL
            );
            $statementBuilder->increaseOffsetBy(
                StatementBuilder::SUGGESTED_PAGE_LIMIT
            );
            $rows = $resultSet->getRows();
        } while (!empty($rows));


        $output = [];
        $header = Pql::getColumnLabels($combinedResultSet);
        
        foreach (Pql::resultSetTo2DimensionStringArray($combinedResultSet) as $array) {
        	$foo = [];
        	$i = 0;
        	foreach ($array as $element) {
        		$foo[$header[$i]] = $element;
        		$i++;
        	}
        	array_push($output, $foo);
        }
        return $output;
	}

	
}
