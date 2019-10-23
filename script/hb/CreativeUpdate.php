<?php

putenv('HOME='.dirname(__DIR__)."/../");
require __DIR__.'/../../vendor/autoload.php';

require __DIR__.'/../../customerConfig/Bayard.php';


use App\Scripts\HeaderBiddingScript;

$applicationName = "Insideall - Test 1";
$jsonKeyFilePath = "/home/gabriel/dfp/googleServiceAccount.json";
$scopes = "https://www.googleapis.com/auth/dfp";
$impersonatedEmail = "insideall@headerbidding-199413.iam.gserviceaccount.com";


$credentials = array(
	"networkCode" => $networkCode,
	"applicationName" => $applicationName,
	"jsonKeyFilePath" => $jsonKeyFilePath,
	"impersonatedEmail" => $impersonatedEmail  
);



$script = new HeaderBiddingScript();

$script->setCredentials($credentials)
	->updateCreatives($entry, "old")
	->clearCredentials();
