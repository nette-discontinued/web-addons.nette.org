<?php

namespace NetteAddons\Api;

use NetteAddons\Model\Addons,
	NetteAddons\Model\AddonDownloads,
	NetteAddons\Model\AddonVersions,
	NetteAddons\Model\Utils\Composer;



/**
 * @author Jan Marek
 * @author Jan Tvrdík
 * @author Jan Dolecek <juzna.cz@gmail.com>
 * @author Patrik Votoček
 */
class ComposerPresenter extends \NetteAddons\BasePresenter
{
	/** @var Addons */
	private $addons;

	/** @var AddonDownloads */
	private $addonDownloads;

	/** @var AddonVersions */
	private $addonVersions;



	/**
	 * @param Addons
	 * @param AddonDownloads
	 * @param AddonVersions
	 */
	public function injectAddons(Addons $addons, AddonDownloads $downloads, AddonVersions $versions)
	{
		$this->addons = $addons;
		$this->addonDownloads = $downloads;
		$this->addonVersions = $versions;
	}



	public function renderPackages()
	{
		$addons = $this->addons->findAll();
		$addons = array_map('NetteAddons\Model\Addon::fromActiveRow', iterator_to_array($addons));

		$packagesJson = Composer::createPackagesJson($addons);
		$packagesJson->notify = str_replace(
			'placeholder', '%package%',
			$this->link('downloadNotify', array('package' => 'placeholder'))
		);
		$this->sendJson($packagesJson);
	}



	/**
	 * Called when composer installs a package to increase counters.
	 *
	 * @link http://getcomposer.org/doc/05-repositories.md#notify
	 * @param string
	 */
	public function actionDownloadNotify($package)
	{
		$post = $this->getRequest()->post;
		if (!isset($post['version'])) {
			$this->error('Invalid request.');
		}
		$version = (string) $post['version'];


		if (!$addonRow = $this->addons->findOneByComposerFullName($package)) {
			$this->error('Package not found.');
		}

		$addon = Addon::fromActiveRow($addonRow);

		$versionRow = $this->addonVersions->findOneBy(array(
			'addonId' => $addon->id,
			'version' => $version,
		));

		if (!$versionRow) {
			$this->error("Version of package not found.");
		}

		$version = AddonVersion::fromActiveRow($versionRow);

		$this->addonDownloads->saveDownload(
			AddonDownloads::TYPE_INSTALL,
			$version->id,
			$this->getHttpRequest()->getRemoteAddress(),
			$this->getHttpRequest()->getHeader('user-agent')
		);
		$this->addons->incrementInstallsCount($addon);
		$this->addonVersions->incrementInstallsCount($version);

		$this->sendJson(array('status' => "success"));
	}
}
