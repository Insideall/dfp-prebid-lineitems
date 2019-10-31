<?php

namespace App\AdManager;

class UserManager extends Manager
{
	protected $user;

	public function getCurrentUser()
	{
		$userService = $this->serviceFactory->createUserService($this->session);

		$user = $userService->getCurrentUser();
		$output = [
			'userId' => $user->getId(),
			'userName' => $user->getName(),
			'userMail' => $user->getEmail(),
			'userRole' => $user->getRoleName(),
		];
		$this->user = $output;

		return $output;
	}

	public function createUser()
	{
		$userService = $this->serviceFactory->createUserService($this->session);
		$user = new User();
		$user->setName('Gabriel');
		$user->setEmail('gabriel@insideall.com');
		//$user->setName($userName);
		$user->setRoleId('-1');
		// Create the users on the server.
		$results = $userService->createUsers([$user]);
		// Print out some information for each created user.
		foreach ($results as $i => $user) {
			printf(
				"%d) User with ID %d and name '%s' was created.\n",
				$i,
				$user->getId(),
				$user->getName()
			);
		}
	}

	public function getUserId()
	{
		$userArray = $this->getCurrentUser();

		return $userArray['userId'];
	}

	public function getAllUsers()
	{
		$userService = $this->serviceFactory->createUserService($this->session);
		$statementBuilder = (new StatementBuilder())->orderBy('id ASC');
		$data = $userService->getUsersByStatement(
			$statementBuilder->toStatement()
		);
		if (null !== $data->getResults()) {
			$totalResultSetSize = $data->getTotalResultSetSize();
			$i = $data->getStartIndex();
			foreach ($data->getResults() as $user) {
				printf(
					"%d) User with ID %d and name '%s' was found.\n",
					$i++,
					$user->getId(),
					$user->getName()
				);
			}
		}
	}
}
