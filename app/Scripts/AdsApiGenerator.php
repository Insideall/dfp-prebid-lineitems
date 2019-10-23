<?php

namespace App\Scripts;

class AdsApiGenerator
{
	protected $networkCode;
	protected $applicationName;
	protected $jsonKeyFilePath;
	protected $scopes = "https://www.googleapis.com/auth/dfp";
	protected $impersonatedEmail;

	public function generateAdsApi()
	{
		if($fp1 = fopen(__DIR__."/../../adsapi_php.ini", 'w')){
			fwrite($fp1, $this->generateContent());
		}
		fclose($fp1);
	}

	public function deleteAdsApi()
	{
		unlink(__DIR__."/../../adsapi_php.ini");
	}

	public function setCredentials($credentials)
	{
		$this->networkCode = $credentials['networkCode'];
		$this->applicationName = $credentials['applicationName'];
		$this->jsonKeyFilePath = $credentials['jsonKeyFilePath'];
		$this->impersonatedEmail = $credentials['impersonatedEmail'];

		return $this;
	} 

	private function generateContent()
	{
		$output = "[AD_MANAGER]\n";
		$output .= "networkCode = \"".$this->networkCode."\"\n";
		$output .= "applicationName = \"".$this->applicationName."\"\n";
		$output .= "[OAUTH2]\n";
		$output .= "jsonKeyFilePath = \"".$this->jsonKeyFilePath."\"\n";
		$output .= "scopes = \"".$this->scopes."\"\n";
		$output .= "impersonatedEmail = \"".$this->impersonatedEmail."\"\n";
		return $output;

	}

	public function setNetworkCode($networkCode)
	{
		$this->networkCode = $networkCode;
		return $this;
	}

	public function setApplicationName($applicationName)
	{
		$this->applicationName = $applicationName;
		return $this;
	}

	public function setJsonKeyFilePath($jsonKeyFilePath)
	{
		$this->jsonKeyFilePath = $jsonKeyFilePath;
		return $this;
	}

	public function setImpersonatedEmail($impersonatedEmail)
	{
		$this->impersonatedEmail = $impersonatedEmail;
		return $this;
	}
}