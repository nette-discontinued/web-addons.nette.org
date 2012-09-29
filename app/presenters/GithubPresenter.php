<?php

namespace NetteAddons;

use NetteAddons\Model\Users;
use NetteAddons\Model\Addon;
use NetteAddons\Model\Addons;

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
		$payload = json_decode($post['payload'], true);
		if (!$payload || !isset($payload['repository']['url'])) {
			$this->getHttpResponse()->setCode(400); // Bad Request
			$this->sendJson(array(
				'status' => 'error',
				'message' => 'Missing or invalid payload'
			));
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

		if (!preg_match('~(github.com/[\w.-]+/[\w.-]+?)(\.git)?$~', $payload['repository']['url'], $match)) {
			$this->getHttpResponse()->setCode(400); // Bad Request
			$this->sendJson(array(
				'status' => 'error',
				'message' => 'Could not parse payload repository URL'
			));
		}

		$repositoryUrl = "https://$match[1]";
		$row = $this->addons->findOneBy(array('repository' => $repositoryUrl));
		$addon = Addon::fromActiveRow($row);

		$importer = $this->context->repositoryImporterFactory->createFromUrl(new \Nette\Http\Url($addon->repository));
		$manager = new \NetteAddons\Model\Facade\AddonManageFacade(NULL, NULL); // FIXME: parameters are not really needed
		$result = $manager->updateVersions($addon, $importer, $this->users->createIdentity($user));

		$this->sendJson(array('status' => "success"));
	}

}
