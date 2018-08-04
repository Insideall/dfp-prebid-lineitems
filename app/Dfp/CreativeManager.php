<?php

namespace App\Dfp;

require(__DIR__."/../../vendor/autoload.php");

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\Util\v201802\StatementBuilder;
use Google\AdsApi\Dfp\v201802\CreativeAsset;
use Google\AdsApi\Dfp\v201802\CreativeService;
use Google\AdsApi\Dfp\v201802\ThirdPartyCreative;
use Google\AdsApi\Dfp\v201802\Size;

class CreativeManager extends DfpManager
{
	protected $ssp;
	protected $advertiserId;

	public function setSsp($ssp)
	{
		$this->ssp = $ssp;
		return $this;
	}

	public function setAdvertiserId($advertiserId)
	{
		$this->advertiserId = $advertiserId;
		return $this;
	}

	public function setUpCreatives()
	{	
		
		$output =  [];
		//Create a creativeName List
		$creativeNameList = [];
		for ($i=1;$i <= 10; $i++) { 
			if(empty($this->ssp)){
				array_push($creativeNameList, "Prebid_Creative_$i");
			} else {
				array_push($creativeNameList, ucfirst($this->ssp)."_Prebid_Creative_$i");
			}
			
		}
		
		foreach($creativeNameList as $creativeName)
		{
			if(empty(($foo = $this->getCreative($creativeName))))
			{
				$foo = $this->createCreative($creativeName, $this->createSnippet(), $this->advertiserId);
				
			}
			array_push($output, $foo[0]);
		}
		return $output;
	}

	public function getAllCreatives()
	{
		$output = [];
		$creativeService = $this->dfpServices->get($this->session, CreativeService::class);
		$pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
		$statementBuilder = (new StatementBuilder())->orderBy('id ASC')
			->limit($pageSize);

		$totalResultSetSize = 0;
		do {
			$data = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
			if($data->getResults() == null)
			{
				return $output;
			}
			foreach ($data->getResults() as $creative) {
			    $foo = array(
			  		"creativeId" => $creative->getId(),
			        "creativeName" => $creative->getName(),
			    );
			    
			    array_push($output, $foo);
			    $statementBuilder->increaseOffsetBy($pageSize);
			}
		} while ($statementBuilder->getOffset() < $totalResultSetSize);

		return $output;
	}

	public function getCreative($creativeName)
	{
		$output = [];
		$creativeService = $this->dfpServices->get($this->session, CreativeService::class);
		$statementBuilder = (new StatementBuilder())
			->orderBy('id ASC')
			->where('name = :name AND advertiserId = :advertiserId')
			->WithBindVariableValue('name', $creativeName)
			->WithBindVariableValue('advertiserId', $this->advertiserId);
		$data = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
		if ($data->getResults() !== null)
		{
			foreach ($data->getResults() as $creative) {
				$foo = array(
					"creativeId"=>$creative->getId(),
					"creativeName"=>$creative->getName()
				);
				array_push($output, $foo);
			}
		}
		return $output;
	}


	public function createCreative($creativeName, $snippet, $advertiserId)
	{
		$output = [];
		$creativeService = $this->dfpServices->get($this->session, CreativeService::class);
		$size = new Size();
        $size->setWidth(1);
        $size->setHeight(1);
        $size->setIsAspectRatio(false);

		$creative = new ThirdPartyCreative();

        $creative->setName($creativeName)
        	->setAdvertiserId($advertiserId)
        	->setIsSafeFrameCompatible(false)
        	->setSnippet($snippet)
        	->setSize($size);

        // Create the order on the server.
        $results = $creativeService->createCreatives([$creative]);
        foreach ($results as $creative) {
			$foo = array(
				"creativeId"=>$creative->getId(),
				"creativeName"=>$creative->getName()
			);
			array_push($output, $foo);
		}
		return $output;
	}

	private function createSnippet()
	{
		if(empty($this->ssp)){
			$key =substr("hb_adid",0,20);
		} else {
			$key =substr("hb_adid_".$this->ssp,0,20);
		}
		$snippet = "<script>\n";
		$snippet .= "var w = window;\n";
		$snippet .= "for (i = 0; i < 10; i++) {\n";
		$snippet .= "\tw = w.parent;\n";
		$snippet .= "\tif (w.pbjs) {\n";
		$snippet .= "\t\ttry {\n";
		$snippet .= "\t\t\tw.pbjs.renderAd(document, '%%PATTERN:".$key."%%');\n";
		$snippet .= "\t\t\tbreak;\n";
		$snippet .= "\t\t} catch (e) {\n";
		$snippet .= "\t\t\tcontinue;\n";
		$snippet .= "\t\t}\n";
		$snippet .= "\t}\n";
		$snippet .= "}\n";
		$snippet .= "</script>\n";
		return $snippet;
	}
}