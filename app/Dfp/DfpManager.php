<?php

namespace App\Dfp;

require("../../vendor/autoload.php");

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\v201802\Order;
use Google\AdsApi\Dfp\v201802\OrderService;
use Google\AdsApi\Dfp\v201802\Company;
use Google\AdsApi\Dfp\v201802\CompanyService;
use Google\AdsApi\Dfp\v201802\CompanyType;

class  DfpManager
{
	protected $dfpServices;
	protected $session;
	
	public function __construct()
	{
		$oAuth2Credential = (new OAuth2TokenBuilder())
			->fromFile()
			->build();
		        
		$this->session = (new DfpSessionBuilder())
			->fromFile()
			->withOAuth2Credential($oAuth2Credential)
			->build();

		$this->dfpServices = new DfpServices();
	}

	public function getDfpServices()
	{
		return $this->dfpServices;
	}

	public function getSession()
	{
		return $this->session;
	}

}