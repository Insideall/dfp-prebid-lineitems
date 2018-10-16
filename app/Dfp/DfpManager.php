<?php

namespace App\Dfp;

require __DIR__.'/../../vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSessionBuilder;

class DfpManager
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
