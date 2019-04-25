<?php

namespace App\AdManager;

require __DIR__.'/../../vendor/autoload.php';

use Google\AdsApi\AdManager\Util\v201811\StatementBuilder;
use Google\AdsApi\AdManager\v201811\ThirdPartyCreative;
use Google\AdsApi\AdManager\v201811\Size;

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

	public function setUpCreatives()
	{
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
				$foo = $this->createCreative($creativeName, $this->createSnippet(), $this->advertiserId);
			} else {
				$foo = $this->updateCreative($creativeName, $this->createSnippet(), $this->advertiserId);
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
		$data = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
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

	public function createCreative($creativeName, $snippet, $advertiserId)
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
			->setIsSafeFrameCompatible(false)
			->setSnippet($snippet)
			->setSize($size);

		// Create the order on the server.
		$results = $creativeService->createCreatives([$creative]);
		foreach ($results as $creative) {
			$foo = [
				'creativeId' => $creative->getId(),
				'creativeName' => $creative->getName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	public function updateCreative($creativeName, $snippet, $advertiserId)
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
			->setIsSafeFrameCompatible(true)
			->setSnippet($snippet)
			->setSize($size);

		// Create the order on the server.
		$results = $creativeService->updateCreatives([$creative]);
		foreach ($results as $creative) {
			$foo = [
				'creativeId' => $creative->getId(),
				'creativeName' => $creative->getName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}


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
	/*
	private function createSnippet()
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

	*/
}
