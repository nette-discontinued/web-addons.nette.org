<?php

namespace NetteAddons;

use NetteAddons\Model\Addon,
	NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVersion,
	NetteAddons\Model\AddonVersions;



/**
 * Misc API calls
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class ApiPresenter extends BasePresenter
{
	/** @var Addons */
	private $addons;

	/** @var AddonVersions */
	private $addonVersions;



	public function injectServices(Addons $addons, AddonVersions $versions)
	{
		$this->addons = $addons;
		$this->addonVersions = $versions;
	}



	/**
	 * Called when composer installs a package to increase counters.
	 *
	 * @link http://getcomposer.org/doc/05-repositories.md#notify
	 * @param string
	 */
	public function actionDownloadNotify($package)
	{
		$post = $this->getRequest()->getPost();
		if (!isset($post['version'])) {
			$this->error('Invalid request');
		}
		$version = (string) $post['version'];

		if (!$addonRow = $this->addons->findOneBy(array('composerName' => $package))) {
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

		$this->addons->incrementInstallsCount($addon);
		$this->addonVersions->incrementInstallsCount($version);

		$this->sendJson(array('status' => "success"));
	}
}
