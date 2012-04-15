<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Http;



/**
 * Addons table repository
 */
class Addons extends Table
{
	/**
	 * @var string Prefix where the uploaded files are stored.
	 */
	private $uploadUri;

	/**
	 * @var string
	 */
	protected $tableName = 'addons';


	public function setUploadUri($uploadUri, Http\IRequest $request)
	{
		$this->uploadUri = rtrim($request->getUrl()->getBaseUrl(), '/') . $uploadUri;
	}



	/**
	 * @param \Nette\Database\Table\Selection $addons
	 * @param string $tag
	 * @return \Nette\Database\Table\Selection
	 */
	public function filterByTag(Selection $addons, $tag)
	{
		$addonIds = $this->connection->table('addons_tags')
			->where('tag_id = ?', $tag)->select('addon_id');

		$addons->where('id', $addonIds);

		return $addons;
	}



	/**
	 * @param Addon|ActiveRow $addon
	 * @param AddonVersion|ActiveRow $version
	 * @return string
	 */
	public function getZipUrl($addon, $version)
	{
		if ($addon->repository) {
			return $addon->repository . '/zipball/' . $version->version;
		} else {
			return $this->uploadUri . '/' . $version->filename;
		}
	}



	public function filterByString(Selection $addons, $string)
	{
		$string = "%$string%";
		$addons->where('name LIKE ? OR short_description LIKE ?', $string, $string);
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addonVersion
	 * @return \Nette\Database\Table\GroupedSelection
	 */
	public function findVersionDependencies(ActiveRow $addonVersion)
	{
		return $addonVersion->related('addons_dependencies');
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 *
	 * @return \Nette\Database\Table\GroupedSelection
	 */
	public function findAddonTags(ActiveRow $addon)
	{
		return $addon->related('addons_tags');
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 *
	 * @return \Nette\Database\Table\GroupedSelection
	 */
	public function findAddonVersions(ActiveRow $addon)
	{
		return $addon->related('addons_versions');
	}

}
