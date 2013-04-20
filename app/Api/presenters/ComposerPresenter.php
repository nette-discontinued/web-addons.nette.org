<?php

namespace NetteAddons\Api;

use NetteAddons\Model\Addon,
	NetteAddons\Model\AddonVersion,
	NetteAddons\Model\Addons,
	NetteAddons\Model\AddonDownloads,
	NetteAddons\Model\AddonVersions,
	NetteAddons\Model\Utils\Composer;



/**
 * @author Jan Marek
 * @author Jan Tvrdík
 * @author Jan Dolecek <juzna.cz@gmail.com>
 * @author Patrik Votoček
 */
final class ComposerPresenter extends \NetteAddons\BasePresenter
{
	/**
	 * @var \NetteAddons\Model\Addons
	 * @inject
	 */
	public $addons;

	/**
	 * @var \NetteAddons\Model\AddonDownloads
	 * @inject
	 */
	public $addonDownloads;

	/**
	 * @var \NetteAddons\Model\AddonVersions
	 * @inject
	 */
	public $addonVersions;



	public function renderPackages()
	{
		$addons = $this->addons->findAll();
		$addons = array_map('NetteAddons\Model\Addon::fromActiveRow', iterator_to_array($addons));

		$packagesJson = Composer::createPackagesJson($addons);
		$packagesJson->notify = str_replace(
			'placeholder', '%package%',
			$this->link('//downloadNotify', array('package' => 'placeholder'))
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

		$this->sendJson(array('status' => "success"));
	}
}
