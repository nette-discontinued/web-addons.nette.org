<?php

namespace NetteAddons;

use NetteAddons\Model\Addons;
use NetteAddons\Model\AddonVersions;



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

		if (!$row = $this->addons->findOneBy(array('composerName' => $package))) {
			$this->error('Package not found.');
		}

		$rowVersion = $this->addonVersions->findOneBy(array(
			'addonId' => $row->id,
			'version' => $version,
		));
		if (!$rowVersion) {
			$this->error("Version of package not found.");
		}

		$row->update(array(
			'totalDownloadsCount' => $row->totalDownloadsCount + 1,
		));

		$rowVersion->update(array(
			'downloadsCount' => $rowVersion->downloadsCount + 1,
		));

		$this->sendJson(array('status' => "success"));
	}
}
