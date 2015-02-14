<?php

namespace NetteAddons\Services;

use DateTime;
use Nette\Database\Context;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;
use NetteAddons\Model\AddonResources;
use NetteAddons\Model\AddonVersion;
use NetteAddons\Model\Importers\IAddonVersionsImporter;
use NetteAddons\Model\Utils\Composer;

class AddonUpdaterService extends \Nette\Object
{
	/** @var \Nette\Database\Context */
	private $db;
	/** @var \NetteAddons\Model\Importers\IAddonVersionsImporter[]|array */
	private $addonVersionsImporters = array();

	public function __construct(Context $db)
	{
		$this->db = $db;
	}

	public function addImporter(IAddonVersionsImporter $addonVersionsImporter)
	{
		$this->addonVersionsImporters[] = $addonVersionsImporter;
	}

	/**
	 * @param int
	 */
	public function updateAddon($id)
	{
		foreach ($this->db->table('addons_resources')->where('addonId = ?', $id) as $row) {
			foreach ($this->addonVersionsImporters as $addonVersionImporter) {
				if ($addonVersionImporter->isSupported($row->resource)) {
					$this->processUpdate($addonVersionImporter, $row->resource, $id);
					break;
				}
			}
		}
	}

	/**
	 * @param \NetteAddons\Model\Importers\IAddonVersionsImporter
	 * @param string
	 * @param int
	 */
	private function processUpdate(IAddonVersionsImporter $addonVersionImporter, $url, $id)
	{
		try {
			$this->db->beginTransaction();

			$addon = $addonVersionImporter->getAddon($url);

			/** @var \Nette\Database\Table\ActiveRow $row */
			$row = $this->db->table('addons')->get($id);
			if (!$row) {
				$this->db->rollBack();
				return;
			}

			$row->update(array(
				'composerVendor' => $addon->getComposerVendor(),
				'composerName' => $addon->getComposerName(),
				'shortDescription' => Strings::truncate($addon->getPerex(), 250),
				'stars' => $addon->getStars(),
			));

			$this->db->table('addons_versions')->where('addonId = ?', $id)->delete();

			$row = $this->db->table('addons_resources')->where('addonId = ? AND type = ?', $id, AddonResources::RESOURCE_PACKAGIST)->fetch();
			if ($row) {
				if ($addon->getPackagist() === null) {
					$row->delete();
				} else {
					$row->update(array(
						'resource' => $addon->getPackagist(),
					));
				}
			} elseif ($addon->getPackagist() !== null) {
				$this->db->table('addons_resources')->insert(array(
					'addonId' => $id,
					'type' => AddonResources::RESOURCE_PACKAGIST,
					'resource' => $addon->getPackagist(),
				));
			}

			$row = $this->db->table('addons_resources')->where('addonId = ? AND type = ?', $id, AddonResources::RESOURCE_GITHUB)->fetch();
			if ($row) {
				if ($addon->getGithub() === null) {
					$row->delete();
				} else {
					$row->update(array(
						'resource' => $addon->getGithub(),
					));
				}
			} elseif ($addon->getGithub() !== null) {
				$this->db->table('addons_resources')->insert(array(
					'addonId' => $id,
					'type' => AddonResources::RESOURCE_GITHUB,
					'resource' => $addon->getGithub(),
				));
			}

			foreach ($addon->getVersions() as $version) {
				/** @var \Nette\Database\Table\ActiveRow $row */
				$row = $this->db->table('addons_versions')->insert(array(
					'addonId' => $id,
					'version' => $version->getVersion(),
					'license' => implode(', ', $version->getLicenses()),
					'distType' => 'zip', // @todo remove
					'distUrl' => 'http://nette.org', // @todo remove
					'updatedAt' => new DateTime,
					'composerJson' => '',
				));

				if (!$row) {
					$this->db->rollBack();
					return;
				}

				foreach ($version->getDependencies() as $dependency) {
					// @todo addon link

					$this->db->table('addons_dependencies')->insert(array(
						'versionId' => $row->id,
						'packageName' => $dependency->getDependencyName(),
						'version' => $dependency->getDependencyVersion(),
						'type' => $dependency->getType(),
					));
				}
			}

			$this->db->commit();
		} catch (\Exception $e) {
			Debugger::log($e);
			$this->db->rollBack();
		}
	}
}
