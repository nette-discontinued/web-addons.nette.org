<?php

namespace NetteAddons\Api;

use Nette\Utils\Json;
use Nette\Http\IResponse;
use NetteAddons\Model\Addon;
use NetteAddons\Model\Importers\GitHubImporter;


final class GithubPresenter extends \NetteAddons\BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Facade\AddonManageFacade
	 */
	public $manager;

	/**
	 * @inject
	 * @var \NetteAddons\Model\Importers\RepositoryImporterManager
	 */
	public $importerManager;

	/**
	 * @inject
	 * @var \NetteAddons\Model\Users
	 */
	public $users;

	/**
	 * @inject
	 * @var \NetteAddons\Model\Addons
	 */
	public $addons;


	/**
	 * Post receive hook, updates addon info
	 */
	public function actionPostReceive()
	{
		$post = $this->getRequest()->getPost();
		if (!isset($post['payload'], $post['username'], $post['apiToken'])) {
			$this->error('Invalid request.');
		}

		$response = $this->getHttpResponse();

		try {
			$payload = Json::decode($post['payload']);
			if (!isset($payload->repository->url)) {
				$response->setCode(IResponse::S400_BAD_REQUEST);
				$this->sendJson(array(
					'status' => 'error',
					'message' => 'Missing or invalid payload',
				));
			}
		} catch (\Nette\Utils\JsonException $e) {
			$this->error('Invalid request.');
		}

		$username = $post['username'];
		$token = $post['apiToken'];

		$user = $this->users->findOneByName($username);
		if (!$user || $user->apiToken !== $token) {
			$response->setCode(IResponse::S403_FORBIDDEN);
			$this->sendJson(array(
				'status' => 'error',
				'message' => 'Invalid credentials'
			));
		}

		if (!GitHubImporter::isValid($payload->repository->url)) {
			$response->setCode(IResponse::S400_BAD_REQUEST);
			$this->sendJson(array(
				'status' => 'error',
				'message' => 'Could not parse payload repository URL'
			));
		}

		$repositoryUrl = GitHubImporter::normalizeUrl($payload->repository->url);
		$row = $this->addons->findOneBy(array('repository' => $repositoryUrl));
		if (!$row) {
			$this->error('Addon not found.');
		}
		$addon = Addon::fromActiveRow($row);

		$userIdentity = $this->users->createIdentity($user);
		$importer = $this->importerManager->createFromUrl($addon->repository);
		$this->manager->updateVersions($addon, $importer, $userIdentity);

		$this->sendJson(array('status' => "success"));
	}
}
