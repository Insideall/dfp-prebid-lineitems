<?php

namespace App\AdManager;

use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\v201908\ServiceFactory;

class Manager
{
	protected $serviceFactory;
	protected $session;

	public function __construct()
	{
		$oAuth2Credential = (new OAuth2TokenBuilder())
			->fromFile()
			->build();

		$this->session = (new AdManagerSessionBuilder())
			->fromFile()
			->withOAuth2Credential($oAuth2Credential)
			->build();

		$this->serviceFactory = new ServiceFactory();
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
