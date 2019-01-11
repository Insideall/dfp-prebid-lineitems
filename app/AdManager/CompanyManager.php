<?php

namespace App\AdManager;

require __DIR__.'/../../vendor/autoload.php';

use Google\AdsApi\AdManager\v201811\Company;
use Google\AdsApi\AdManager\v201811\CompanyType;
use Google\AdsApi\AdManager\Util\v201811\StatementBuilder;

class CompanyManager extends Manager
{
	public function setUpCompany($companyName)
	{
		if (empty(($foo = $this->getCompany($companyName)))) {
			$foo = $this->createCompany($companyName);
		}

		return $foo[0]['companyId'];
	}

	public function createCompany($companyName)
	{
		$output = [];

		$companyService = $this->serviceFactory->createCompanyService($this->session);
		$company = new Company();
		$company->setName($companyName);
		$company->setType(CompanyType::ADVERTISER);
		// Create the company on the server.
		$data = $companyService->createCompanies([$company]);
		// Print out some information for each created company.
		foreach ($data as $i => $company) {
			$foo = [
				'companyId' => $company->getId(),
				'companyName' => $company->getName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	public function getAllCompanies()
	{
		$output = [];
		$companyService = $this->serviceFactory->createCompanyService($this->session);
		$statementBuilder = (new StatementBuilder())->orderBy('id ASC');
		$data = $companyService->getCompaniesByStatement($statementBuilder->toStatement());
		if (null !== $data->getResults()) {
			foreach ($data->getResults() as $company) {
				$foo = [
					'companyId' => $company->getId(),
					'companyName' => $company->getName(),
				];
				array_push($output, $foo);
			}
		}

		return $output;
	}

	public function getCompany($companyName)
	{
		$output = [];
		$companyService = $this->serviceFactory->createCompanyService($this->session);
		$statementBuilder = (new StatementBuilder())
			->orderBy('id ASC')
			->where('name = :name')
			->WithBindVariableValue('name', $companyName);
		$data = $companyService->getCompaniesByStatement($statementBuilder->toStatement());
		if (null !== $data->getResults()) {
			foreach ($data->getResults() as $company) {
				$foo = [
					'companyId' => $company->getId(),
					'companyName' => $company->getName(),
				];
				array_push($output, $foo);
			}
		}

		return $output;
	}
}
