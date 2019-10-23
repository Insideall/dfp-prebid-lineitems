<?php

namespace App\AdManager;

require __DIR__.'/../../vendor/autoload.php';

use Google\AdsApi\AdManager\Util\v201908\StatementBuilder;
use Google\AdsApi\AdManager\v201908\CreativeService;
use Google\AdsApi\AdManager\v201908\ThirdPartyCreative;
use Google\AdsApi\AdManager\v201908\Size;
use Google\AdsApi\AdManager\v201908\ApiException;

class CreativeManager extends Manager
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

	public function setUpCreatives($type = "old")
	{
		$safeframe = $type == "old" ? false : true;
		
		$output = [];
		//Create a creativeName List
		$creativeNameList = [];
		for ($i = 1; $i <= 10; ++$i) {
			if (empty($this->ssp)) {
				array_push($creativeNameList, "Prebid_Creative_$i");
			} else {
				array_push($creativeNameList, ucfirst($this->ssp)."_Prebid_Creative_$i");
			}
		}

		foreach ($creativeNameList as $creativeName) {
			if (empty(($foo = $this->getCreative($creativeName)))) {
				$foo = $this->createCreative($creativeName, $this->createSnippet($type), $this->advertiserId, $safeframe);
			} else {
				$foo = $this->updateCreative($creativeName, $this->createSnippet($type), $this->advertiserId, $safeframe);
			}
			array_push($output, $foo[0]);
		}

		return $output;
	}

	public function getAllCreatives()
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
		$statementBuilder = (new StatementBuilder())->orderBy('id ASC')
			->limit($pageSize);

		$totalResultSetSize = 0;
		do {
			$data = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
			if (null == $data->getResults()) {
				return $output;
			}
			foreach ($data->getResults() as $creative) {
				$foo = [
					'creativeId' => $creative->getId(),
					'creativeName' => $creative->getName(),
				];

				array_push($output, $foo);
				$statementBuilder->increaseOffsetBy($pageSize);
			}
		} while ($statementBuilder->getOffset() < $totalResultSetSize);

		return $output;
	}

	public function getCreative($creativeName)
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$statementBuilder = (new StatementBuilder())
			->orderBy('id ASC')
			->where('name = :name AND advertiserId = :advertiserId')
			->WithBindVariableValue('name', $creativeName)
			->WithBindVariableValue('advertiserId', $this->advertiserId);
		do{
			try{
				$data = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);
		if (null !== $data->getResults()) {
			foreach ($data->getResults() as $creative) {
				$foo = [
					'creativeId' => $creative->getId(),
					'creativeName' => $creative->getName(),
				];
				array_push($output, $foo);
			}
		}

		return $output;
	}

	public function createCreative($creativeName, $snippet, $advertiserId, $safeframe)
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$size = new Size();
		$size->setWidth(1);
		$size->setHeight(1);
		$size->setIsAspectRatio(false);

		$creative = new ThirdPartyCreative();

		$creative->setName($creativeName)
			->setAdvertiserId($advertiserId)
			->setIsSafeFrameCompatible($safeframe)
			->setSnippet($snippet)
			->setSize($size);

		// Create the order on the server.
		do{
			try{
				$results = $creativeService->createCreatives([$creative]);
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);
		foreach ($results as $creative) {
			$foo = [
				'creativeId' => $creative->getId(),
				'creativeName' => $creative->getName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	public function updateCreative($creativeName, $snippet, $advertiserId, $safeframe)
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$statementBuilder = (new StatementBuilder())->where('name = :name')
            ->orderBy('id ASC')
            ->limit(1)
            ->withBindVariableValue('name', $creativeName);
        // Get the creative.
        $page = $creativeService->getCreativesByStatement(
            $statementBuilder->toStatement()
        );

        $creative = $page->getResults()[0];
		$size = new Size();
		$size->setWidth(1);
		$size->setHeight(1);
		$size->setIsAspectRatio(false);

		$creative->setName($creativeName)
			->setAdvertiserId($advertiserId)
			->setIsSafeFrameCompatible($safeframe)
			->setSnippet($snippet)
			->setSize($size);

		// Create the order on the server.
		do {
			try {
				$results = $creativeService->updateCreatives([$creative]);
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);
		
		foreach ($results as $creative) {
			$foo = [
				'creativeId' => $creative->getId(),
				'creativeName' => $creative->getName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	/*
	private function createSnippet()
	{
		$snippet = "<script src = 'https://cdn.jsdelivr.net/npm/prebid-universal-creative@latest/dist/creative.js'></script>\n";
		$snippet .= "<script>\n";
		$snippet .= "\tvar ucTagData = {};\n";
		$snippet .= "\tucTagData.adServerDomain = '';\n";
		$snippet .= "\tucTagData.pubUrl = '%%PATTERN:url%%';\n";
		$snippet .= "\tucTagData.targetingMap = %%PATTERN:TARGETINGMAP%%;\n";
		$snippet .= "\ttry {\n";
		$snippet .= "\t\tucTag.renderAd(document, ucTagData);\n";
		$snippet .= "\t} catch (e) {\n";
    	$snippet .= "\t\tconsole.log(e);\n";
    	$snippet .= "\t}\n";
    	$snippet .= "</script>\n";

    	return $snippet;

	}
	*/

	private function createSnippet($type)
	{
		if($type == "old"){
			return $this->createOldSnippet();
		} else {
			return $this->createNewSnippet();
		}
	}

	private function createOldSnippet()
	{
		if (empty($this->ssp)) {
			$key = substr('hb_adid', 0, 20);
		} else {
			$key = substr('hb_adid_'.$this->ssp, 0, 20);
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

	private function createNewSnippet()
	{
		$snippet = "<script src = 'https://cdn.jsdelivr.net/npm/prebid-universal-creative@latest/dist/creative.js'></script>\n";
		$snippet .= "<script>\n";
		$snippet .= "\tvar ucTagData = {};\n";
		$snippet .= "\tucTagData.adServerDomain = '';\n";
		$snippet .= "\tucTagData.pubUrl = '%%PATTERN:url%%';\n";
		$snippet .= "\tucTagData.targetingMap = %%PATTERN:TARGETINGMAP%%;\n";
		$snippet .= "\tucTagData.hbPb = \"%%PATTERN:hb_pb%%\";\n";
		$snippet .= "\ttry {\n";
		$snippet .= "\t\tucTag.renderAd(document, ucTagData);\n";
		$snippet .= "\t} catch (e) {\n";
    	$snippet .= "\t\tconsole.log(e);\n";
    	$snippet .= "\t}\n";
    	$snippet .= "</script>\n";

    	return $snippet;

	}

	
}
