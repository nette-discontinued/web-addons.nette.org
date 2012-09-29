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
	 * Called when composer installs a package to increase counters
	 * @param string $package
	 */
	public function actionDownloadNotify($package)
	{
		if (!$row = $this->addons->findOneBy(array('composerName' => $package))) {
			throw new \Nette\Application\BadRequestException("Package not found", 404);
		}

		$version = $this->request->post['version'];
		$rowVersion = $this->addonVersions->findOneBy(array(
			'addonId' => $row->id,
			'version' => $version,
		));
		if (!$row) {
			throw new \Nette\Application\BadRequestException("Version of package not found", 404);
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
