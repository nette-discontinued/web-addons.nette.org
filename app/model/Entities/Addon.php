<?php

namespace NetteAddons\Model;

use Nette\DateTime;
use Nette\Utils\Strings;
use Nette\Database\Table\ActiveRow;


/**
 * @property string $composerFullName
 */
class Addon extends \Nette\Object
{
	const COMPOSER_NAME_RE = '#^(?P<vendor>[a-z0-9]+(-[a-z0-9]+)*)/(?P<name>[a-z0-9]+(-[a-z0-9]+)*)$#i';

	const TYPE_COMPOSER = 'composer';
	const TYPE_DOWNLOAD = 'download';

	/** @var int */
	public $id;

	/** @var string */
	public $name;

	/** @var string */
	public $composerVendor;

	/** @var string */
	public $composerName;

	/** @var int */
	public $userId;

	/** @var string single line description */
	public $shortDescription;

	/** @var string */
	public $description;

	/** @var string */
	public $descriptionFormat = 'texy';

	/** @var string default license for new versions */
	public $defaultLicense;

	/** @var string|NULL repository URL */
	public $repository;

	/** @var string|NULL */
	public $repositoryHosting;

	/** @var string|NULL URL to addon demo. */
	public $demo;

	/** @var DateTime */
	public $updatedAt;

	/** @var AddonVersion[] (versionNumber => AddonVersion) */
	public $versions = array();

	/** @var \stdClass */
	public $votes;

	/** @var Tag[]|string[]|int[] (tagId => Tag (from db) or # => tagName (new user-created tags) or # => tagId */
	public $tags = array();

	/** @var DateTime */
	public $deletedAt;

	/** @var \Nette\Database\Table\ActiveRow|NULL userId */
	public $deletedBy;

	/** @var string */
	public $type;

	/** @var array */
	public $resources = array();

	/** @var integer */
	public $stars;


	/**
	 * Creates Addon entity from Nette\Database row.
	 *
	 * @todo   Consider lazy loading for versions and tags.
	 *
	 * @param \Nette\Database\Table\ActiveRow
	 * @param AddonVotes
	 * @return Addon
	 */
	public static function fromActiveRow(ActiveRow $row, AddonVotes $addonVotes = NULL)
	{
		$addon = new static;
		$addon->id = (int) $row->id;
		$addon->name = $row->name;
		$addon->composerVendor = $row->composerVendor;
		$addon->composerName = $row->composerName;
		$addon->userId = (int) $row->user->id;
		$addon->shortDescription = $row->shortDescription;
		$addon->description = $row->description;
		$addon->descriptionFormat = $row->descriptionFormat;
		$addon->defaultLicense = $row->defaultLicense;
		$addon->repository = $row->repository;
		$addon->repositoryHosting = $row->repositoryHosting;
		$addon->demo = $row->demo;
		$addon->updatedAt = ($row->updatedAt ? DateTime::from($row->updatedAt) : NULL);
		$addon->deletedAt = $row->deletedAt;
		$addon->deletedBy = $row->ref('deletedBy');
		$addon->type = $row->type;
		$addon->stars = $row->stars;

		foreach ($row->related('versions') as $versionRow) {
			$version = AddonVersion::fromActiveRow($versionRow);
			$version->addon = $addon;
			$addon->versions[$version->version] = $version;
		}

		foreach ($row->related('tags') as $tagRow) {
			$addon->tags[$tagRow->tag->id] = Tag::fromActiveRow($tagRow->tag);
		}

		foreach ($row->related('addons_resources') as $resourceRow) {
			$addon->resources[$resourceRow->type] = $resourceRow->resource;
		}

		if ($addonVotes) {
			$addon->votes = $addonVotes->calculatePopularity($row);
		}

		return $addon;
	}


	/**
	 * @return int[]
	 */
	public function getTagsIds()
	{
		$ids = array();
		foreach ($this->tags as $tag) {
			if ($tag instanceof Tag) {
				$ids[] = $tag->id;
			} elseif (is_int($tag) || ctype_digit($tag)) {
				$ids[] = (int) $tag;
			}
		}
		return $ids;
	}


	/**
	 * @return string|NULL (vendor/name)
	 */
	public function getComposerFullName()
	{
		if (!$this->composerVendor || !$this->composerName) {
			return NULL;
		}
		return $this->composerVendor . '/' . $this->composerName;
	}


	/**
	 * @param string (vendor/name)
	 * @return Addon
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function setComposerFullName($composerFullName)
	{
		if (($data = Strings::match($composerFullName, static::COMPOSER_NAME_RE)) === NULL) {
			throw new \NetteAddons\InvalidArgumentException('Invalid full composer name format.');
		}
		$this->composerVendor = $data['vendor'];
		$this->composerName = $data['name'];
		return $this;
	}
}
