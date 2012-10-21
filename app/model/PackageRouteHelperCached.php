<?php

namespace NetteAddons\Model;

use NetteAddons;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette;



/**
 * @author Pavel Kučera
 */
class PackageRouteHelperCached extends PackageRouteHelper
{
	/** @var string */
	const EXPIRE_TIME = '+1 week';

	/** @var Cache */
	private $cache;


	/**
	 * @param Addons
	 * @param IStorage
	 */
	public function __construct(Addons $addons, IStorage $cacheStorage)
	{
		parent::__construct($addons);
		$this->cache = new Cache($cacheStorage, 'Route');
	}



	/**
	 * @param string
	 * @return int|NULL
	 */
	public function filterIn($composerName)
	{
		$cache = $this->cache;
		$cacheKey = "name: $composerName";
		$id = $cache->load($cacheKey);
		if (!$id && $id = parent::filterIn($composerName)) {
			$cache->save($cacheKey, $id, array(
				$cache::EXPIRE => $this::EXPIRE_TIME,
				$cache::TAGS => array(
					"id#$id"
				),
			));
		}
		return $id;
	}



	/**
	 * @param int
	 * @return string|NULL
	 */
	public function filterOut($id)
	{
		$cache = $this->cache;
		$cacheKey = "id: $id";
		$composerName = $cache->load($cacheKey);
		if (!$composerName && $composerName = parent::filterOut($id)) {
			$composerName = parent::filterOut($id);
			$cache->save($cacheKey, $composerName, array(
				$cache::EXPIRE => $this::EXPIRE_TIME,
				$cache::TAGS => array(
					"id#$id"
				),
			));
		}
		return $composerName;
	}


	/**
	 * @param Addon
	 * @return void
	 */
	public function cleanAddonCache(Addon $addon)
	{
		$id = $addon->id;
		$cache = $this->cache;
		$cache->clean(array(
			$cache::TAGS => array(
				"id#$id",
			)
		));
	}
}
