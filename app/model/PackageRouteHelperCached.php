<?php

namespace NetteAddons\Model;

use Nette\Caching\Cache,
	Nette\Caching\IStorage;



/**
 * @author Pavel Kučera
 */
class PackageRouteHelperCached extends PackageRouteHelper
{
	/** @var string */
	const EXPIRE_TIME = '+1 week',
		CACHE_NAMESPACE = 'Route';

	/** @var Cache */
	private $cache;



	/**
	 * @param Addons
	 * @param \Nette\Caching\IStorage
	 */
	public function __construct(Addons $addons, IStorage $cacheStorage)
	{
		parent::__construct($addons);
		$this->cache = new Cache($cacheStorage, static::CACHE_NAMESPACE);

		$route = $this;
		$addons->onAddonChange[] = function(Addon $addon) use($route) {
			$route->cleanAddonCache($addon);
		};
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
