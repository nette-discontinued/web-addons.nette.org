<?php

namespace NetteAddons\Model;

use NetteAddons;
use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\DateTime;
use Nette\Http;



/**
 * Addons table repository
 */
class Addons extends Table
{
	/** @var string Prefix where the uploaded files are stored. */
	private $uploadUri;

	/** @var string */
	protected $tableName = 'addons';

	/** @var AddonVersions versions repository */
	private $versions;

	/** @var Tags tags repository */
	private $tags;



	public function __construct(Nette\Database\Connection $dbConn, AddonVersions $versions, Tags $tags)
	{
		parent::__construct($dbConn);
		$this->versions = $versions;
		$this->tags = $tags;
	}




// === Selecting addons ========================================================

	/**
	 * Filter addons selection by tag.
	 *
	 * @param  \Nette\Database\Table\Selection
	 * @param  int tag id
	 * @return \Nette\Database\Table\Selection for fluent interface
	 */
	public function filterByTag(Selection $addons, $tagId)
	{
		$addonIds = $this->connection->table('addons_tags')
			->where('tagId = ?', $tagId)->select('addonId');

		return $addons->where('id', $addonIds);
	}



	/**
	 * Filter addon selection by some text.
	 *
	 * @param  \Nette\Database\Table\Selection
	 * @param  string
	 * @return \Nette\Database\Table\Selection for fluent interface
	 */
	public function filterByString(Selection $addons, $string)
	{
		$string = "%$string%";
		return $addons->where('name LIKE ? OR shortDescription LIKE ?', $string, $string);
	}



// === CRUD ====================================================================

	/**
	 * Saves addos to database.
	 *
	 * @author Jan Tvrdík
	 * @param  Addon
	 * @return \Nette\Database\Table\ActiveRow created row
	 * @throws \NetteAddons\DuplicateEntryException if addons with given composer name already exists
	 */
	public function add(Addon $addon)
	{
		if ($addon->id !== NULL) {
			throw new \NetteAddons\InvalidArgumentException('Addon already has an ID.');
		}

		if (count($addon->versions) < 1) {
			throw new \NetteAddons\InvalidArgumentException('Addon must have at least one version.');
		}

		$this->connection->beginTransaction();

		try {
			$addonRow = $this->createRow(array(
				'name'             => $addon->name,
				'composerName'     => $addon->composerName,
				'userId'           => $addon->userId, // author
				'shortDescription' => $addon->shortDescription,
				'description'      => $addon->description,
				'defaultLicense'   => $addon->defaultLicense,
				'repository'       => $addon->repository,
				'demo'             => $addon->demo,
				'updatedAt'        => new Datetime('now'),
			));

			foreach ($addon->versions as $version) {
				try {
					$versionRow = $this->versions->add($addon, $version);
					// $this->dependencies->setVersionDependencies($versionRow, $version); // move to versions

				} catch (\NetteAddons\InvalidArgumentException $e) {
					throw new \NetteAddons\InvalidStateException("Cannot create version {$version->version}.", NULL, $e);
				}
			}

			foreach ($addon->tags as $tag) {
				$this->tags->addAddonTag($addonRow, $tag);
			}

			$this->connection->commit();
			return $addonRow;

		} catch (\Exception $e) {
			$this->connection->rollBack();
			throw $e;
		}
	}



	public function setUploadUri($uploadUri, Http\IRequest $request)
	{
		$this->uploadUri = rtrim($request->getUrl()->getBaseUrl(), '/') . $uploadUri;
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

}
