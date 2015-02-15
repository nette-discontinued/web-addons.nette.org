<?php

namespace NetteAddons\Model;

use Nette\Utils\Strings;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\Selection;
use Nette\DateTime;


class Addons extends Table
{
	/** @var array */
	public $onAddonChange = array();

	/** @var string */
	protected $tableName = 'addons';

	/** @var AddonVersions versions repository */
	private $versions;

	/** @var Tags tags repository */
	private $tags;


	public function __construct(Context $db, AddonVersions $versions, Tags $tags)
	{
		parent::__construct($db);
		$this->versions = $versions;
		$this->tags = $tags;
	}


	/**
	 * @param bool
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable($ignoreDeleted = FALSE)
	{
		$table = parent::getTable();

		if (!$ignoreDeleted) {
			$this->filterOutDeleted($table);
		}

		return $table;
	}


	/**
	 * @param int
	 * @param bool
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function find($id, $ignoreDeleted = FALSE)
	{
		return $this->getTable($ignoreDeleted)->wherePrimary($id)->fetch();
	}


	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function findDeleted()
	{
		return $this->getTable(TRUE)->where('deletedAt IS NOT NULL');
	}


	/**
	 * @param  \Nette\Database\Table\Selection
	 * @param  bool
	 * @return array[]
	 */
	public function findGroupedByCategories($tags, $ignoreDeleted = FALSE)
	{
		$result = array();
		foreach ($tags as $tag) {
			$result[$tag->id] = array();
			$addons = $tag->related('addons_tags')->order('addon.stars DESC');
			if (!$ignoreDeleted) {
				$this->filterOutDeleted($addons);
			}
			foreach ($addons as $addon_tag) {
				$result[$tag->id][] = $addon_tag->addon;
			}
		}
		return $result;
	}


	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function findVendors()
	{
		return $this->getTable()->group('composerVendor')->order('updatedAt DESC');
	}


	/**
	 * @param int|NULL
	 * @param bool
	 * @return \Nette\Database\Table\Selection
	 */
	public function findLastUpdated($count = NULL, $ignoreDeleted = FALSE)
	{
		$selection = $this->getTable($ignoreDeleted)->order('updatedAt DESC');

		if (!is_null($count)) {
			$selection->limit($count);
		}

		return $selection;
	}


	/**
	 * @param  int|NULL
	 * @param  bool
	 * @return \Nette\Database\Table\Selection
	 */
	public function findMostFavorited($count = NULL, $ignoreDeleted = FALSE)
	{
		$selection = $this->getTable($ignoreDeleted)->group('id')->order('SUM(:addons_vote.vote) DESC');
		if (!is_null($count)) {
			$selection->limit($count);
		}
		return $selection;
	}


	/**
	 * @param int
	 * @return \Nette\Database\Table\Selection
	 */
	public function findByUser($userId)
	{
		return $this->findBy(array('userId' => $userId));
	}


	/**
	 * @param string
	 * @return \Nette\Database\Table\Selection
	 */
	public function findByComposerVendor($vendor)
	{
		return $this->findBy(array('composerVendor' => $vendor));
	}


	/**
	 * @param string
	 * @return \Nette\Database\Table\Selection
	 */
	public function findOneByComposerFullName($composerFullName)
	{
		$composerVendor = $composerName = NULL;
		if (($data = Strings::match($composerFullName, Addon::COMPOSER_NAME_RE)) !== NULL) {
			$composerVendor = $data['vendor'];
			$composerName = $data['name'];
		}
		return $this->findOneByComposerVendorAndName($composerVendor, $composerName);
	}


	/**
	 * @param string
	 * @param string
	 * @return \Nette\Database\Table\Selection
	 */
	public function findOneByComposerVendorAndName($vendor, $name)
	{
		return $this->findOneBy(array('composerVendor' => $vendor, 'composerName' => $name));
	}


	/**
	 * Filter addons selection by tag.
	 *
	 * @param  \Nette\Database\Table\Selection
	 * @param  int tag id
	 * @return \Nette\Database\Table\Selection for fluent interface
	 */
	public function filterByTag(Selection $addons, $tagId)
	{
		$addonIds = $this->db->table('addons_tags')
			->where('tagId = ?', $tagId)->select('addonId');

		return $addons->where('id', $addonIds);
	}


	/**
	 * Filter addon selection by some text.
	 *
	 * @param \Nette\Database\Table\Selection
	 * @param string
	 * @return \Nette\Database\Table\Selection for fluent interface
	 */
	public function filterByString(Selection $addons, $string)
	{
		$string = "%$string%";
		return $addons->where('name LIKE ? OR shortDescription LIKE ?', $string, $string);
	}


	/**
	 * @param Addon
	 * @param \Nette\Security\IIdentity
	 */
	public function markAsDeleted(Addon $addon, \Nette\Security\IIdentity $user)
	{
		$row = $this->find($addon->id);

		if (!$row) {
			return;
		}

		$row->update(array(
			'deletedAt' => new \DateTime,
			'deletedBy' => $user->getId(),
		));

		$this->onAddonChange($addon);
	}


	public function unmarkAsDeleted(Addon $addon)
	{
		$row = $this->find($addon->id, TRUE);

		if (!$row) {
			return;
		}

		$row->update(array(
			'deletedAt' => NULL,
			'deletedBy' => NULL,
		));

		$this->onAddonChange($addon);
	}


	/**
	 * Saves addon to database.
	 *
	 * @param Addon
	 * @return \Nette\Database\Table\ActiveRow created row
	 * @throws \NetteAddons\DuplicateEntryException
	 * @throws \NetteAddons\InvalidArgumentException
	 * @throws \PDOException
	 */
	public function add(Addon $addon)
	{
		if ($addon->id !== NULL) {
			throw new \NetteAddons\InvalidArgumentException('Addon already has an ID.');
		}

		if (count($addon->versions) < 1) {
			throw new \NetteAddons\InvalidArgumentException('Addon must have at least one version.');
		}

		$this->db->beginTransaction();
		try {
			$row = $this->createRow(array(
				'name' => $addon->name,
				'composerVendor' => $addon->composerVendor,
				'composerName' => $addon->composerName,
				'userId' => $addon->userId,
				'repository' => $addon->repository,
				'repositoryHosting' => $addon->repositoryHosting,
				'shortDescription' => $addon->shortDescription,
				'description' => $addon->description,
				'descriptionFormat' => $addon->descriptionFormat,
				'demo' => $addon->demo ?: NULL,
				'defaultLicense' => $addon->defaultLicense,
				'updatedAt' => new Datetime('now'),
				'deletedAt' => $addon->deletedAt,
				'deletedBy' => $addon->deletedBy,
				'type' => $addon->type,
			));

			$addon->id = $row->id;
			foreach ($addon->versions as $version) {
				$this->versions->add($version);
			}

			foreach ($addon->resources as $resourceType => $resource) {
				$row = $this->db->table('addons_resources')->where('addonId = ? AND type = ?', $addon->id, $resourceType)->fetch();
				if ($row) {
					$row->update(array(
						'resource' => $resource,
					));
				} else {
					$this->db->table('addons_resources')->insert(array(
						'addonId' => $addon->id,
						'type' => $resourceType,
						'resource' => $resource,
					));
				}
			}

			$this->tags->saveAddonTags($addon);

			$this->db->commit();

			$this->onAddonChange($addon);

			return $row;
		} catch (\Exception $e) {
			$this->db->rollBack();
			$addon->id = NULL;
			throw $e;
		}
	}


	public function update(Addon $addon)
	{
		// TODO: this may fail, becase find() may return FALSE
		$this->find($addon->id)->update(array(
			'name' => $addon->name,
			'repository' => $addon->repository,
			'repositoryHosting' => $addon->repositoryHosting,
			'shortDescription' => $addon->shortDescription,
			'description' => $addon->description,
			'descriptionFormat' => $addon->descriptionFormat,
			'demo' => $addon->demo ?: NULL,
			'defaultLicense' => $addon->defaultLicense,
			'updatedAt' => new Datetime('now'),
			'deletedAt' => $addon->deletedAt,
			'deletedBy' => $addon->deletedBy,
			'type' => $addon->type,
		));

		$this->onAddonChange($addon);

		$this->tags->saveAddonTags($addon);
	}


	public function delete(Addon $addon)
	{
		$row = $this->find($addon->id, TRUE);

		if (!$row) {
			return;
		}

		$row->delete();

		$this->onAddonChange($addon);
	}


	/**
	 * @param  \Nette\Database\Table\Selection
	 */
	private function filterOutDeleted(Selection $selection)
	{
		$selection->where('deletedAt IS NULL');
	}
}
