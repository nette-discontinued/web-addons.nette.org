<?php

namespace NetteAddons\Api;

use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonVersion;
use NetteAddons\Model\AddonDownloads;
use NetteAddons\Model\Utils\Composer;


final class ComposerPresenter extends \NetteAddons\BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Addons
	 */
	public $addons;

	/**
	 * @inject
	 * @var \NetteAddons\Model\AddonDownloads
	 */
	public $addonDownloads;

	/**
	 * @inject
	 * @var \NetteAddons\Model\AddonVersions
	 */
	public $addonVersions;


	public function renderPackages()
	{
		$addons = $this->addons->findAll();
		$addons = array_map('NetteAddons\Model\Addon::fromActiveRow', iterator_to_array($addons));

		$packagesJson = Composer::createPackagesJson($addons);
		$packagesJson->notify = str_replace(
			'placeholder',
			'%package%',
			$this->link('//downloadNotify', array('package' => 'placeholder'))
		);
		$this->sendJson($packagesJson);
	}


	/**
	 * Called when composer installs a package to increase counters.
	 *
	 * @link http://getcomposer.org/doc/05-repositories.md#notify
	 *
	 * @param string
	 */
	public function actionDownloadNotify($package)
	{
		$post = $this->getRequest()->getPost();
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
			$this->error('Version of package not found.');
		}

		$version = AddonVersion::fromActiveRow($versionRow);

		$this->addonDownloads->saveDownload(
			AddonDownloads::TYPE_INSTALL,
			$version->id,
			$this->getHttpRequest()->getRemoteAddress(),
			$this->getHttpRequest()->getHeader('user-agent')
		);

		$this->sendJson(array('status' => 'success'));
	}
}
