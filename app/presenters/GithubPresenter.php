<?php

namespace NetteAddons;

use Nette\Utils\Json,
	NetteAddons\Model\Addon,
	NetteAddons\Model\Addons,
	NetteAddons\Model\Users;



/**
 * GitHub API
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class GithubPresenter extends BasePresenter
{
	/** @var Users */
	private $users;

	/** @var Addons */
	private $addons;



	public function injectServices(Users $users, Addons $addons)
	{
		$this->users = $users;
		$this->addons = $addons;
	}



	/**
	 * Post receive hook, updates addon info
	 */
	public function actionPostReceive()
	{
		$post = $this->getRequest()->getPost();
		if (!isset($post['payload'], $post['username'], $post['apiToken'])) {
			$this->error('Invalid request.');
		}

		try {
			$payload = Json::decode($post['payload']);
			if (!isset($payload->repository->url)) {
				$this->getHttpResponse()->setCode(400); // Bad Request
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
			$this->getHttpResponse()->setCode(403); // Forbidden
			$this->sendJson(array(
				'status' => 'error',
				'message' => 'Invalid credentials'
			));
		}

		if (!preg_match('~(github.com/[\w.-]+/[\w.-]+?)(\.git)?$~', $payload->repository->url, $match)) {
			$this->getHttpResponse()->setCode(400); // Bad Request
			$this->sendJson(array(
				'status' => 'error',
				'message' => 'Could not parse payload repository URL'
			));
		}

		$repositoryUrl = "https://$match[1]";
		$row = $this->addons->findOneBy(array('repository' => $repositoryUrl));
		if (!$row) $this->error();
		$addon = Addon::fromActiveRow($row);

		$importer = $this->context->repositoryImporterManager->createFromUrl($addon->repository);
		$manager = new \NetteAddons\Model\Facade\AddonManageFacade(NULL, NULL); // FIXME: parameters are not really needed
		$result = $manager->updateVersions($addon, $importer, $this->users->createIdentity($user));

		$this->sendJson(array('status' => "success"));
	}
}
