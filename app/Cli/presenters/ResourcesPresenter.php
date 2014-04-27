<?php

namespace NetteAddons\Cli;

use Nette\Database\Table\ActiveRow;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Json;
use Nette\Utils\Strings;

class ResourcesPresenter extends BasePresenter
{
	/** @var \Nette\Database\Context */
	private $db;

	public function __construct(\Nette\Database\Context $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function actionDefault()
	{
		try {
			$this->db->beginTransaction();

			$this->db->table('addons_resources')->delete();

			foreach ($this->db->table('addons') as $addon)
			$this->updateAddon($addon);

			$this->db->commit();
		} catch (\PDOException $e) {
			Debugger::log($e);
			$this->db->rollBack();
		}
	}

	private function updateAddon(ActiveRow $addon)
	{
		$this->writeln('Updating: ' . $addon->name);

		$github = $this->normalizeGithubUrl($addon->repository);
		if ($github) {
			$guzzle = new \Guzzle\Http\Client;
			try {
				$guzzle->get($github)->send();

				$this->db->table('addons_resources')->insert(array(
					'addonId' => $addon->id,
					'type' => 'github',
					'resource' => $github,
				));
			} catch (\Guzzle\Http\Exception\RequestException $e) {
				$this->writeln((string) $e);
			}
		}

		if ($addon->type === 'composer') {
			$version = $addon->related('versions')->order('id', 'DESC')->fetch();
			$composerData = Json::decode($version->composerJson);
			$packagist = $this->generatePackagistUrl($composerData->name);

			$guzzle = new \Guzzle\Http\Client;
			try {
				$guzzle->get($packagist . '.json')->send();

				$this->db->table('addons_resources')->insert(array(
					'addonId' => $addon->id,
					'type' => 'packagist',
					'resource' => $packagist,
				));
			} catch (\Guzzle\Http\Exception\RequestException $e) {
				$this->writeln((string) $e);
			}
		}

		if ($addon->demo) {
			$guzzle = new \Guzzle\Http\Client;
			try {
				$guzzle->get($addon->demo)->send();

				$this->db->table('addons_resources')->insert(array(
					'addonId' => $addon->id,
					'type' => 'demo',
					'resource' => $addon->demo,
				));
			} catch (\Guzzle\Http\Exception\RequestException $e) {
				$this->writeln((string) $e);
			}
		}
	}

	/**
	 * @param string
	 * @return string|null
	 */
	private function normalizeGithubUrl($url)
	{
		$match = Strings::match($url, '~(?P<url>github\.com/[\w\.\-]+/[\w\.\-]+)~i');
		if (is_array($match) && isset($match['url'])) {
			if (!Strings::match($match['url'], '~gist\.github\.com~')) {
				return 'https://' . $match['url'];
			}
		}

		return null;
	}

	/**
	 * @param string
	 * @return string
	 */
	private function generatePackagistUrl($composer)
	{
		return 'https://packagist.org/packages/' . $composer;
	}
}
