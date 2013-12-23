<?php

namespace NetteAddons;

use Nette\Application\Routers\Route;

/**
 * Router factory.
 */
class RouterFactory
{
	/** @var \NetteAddons\Model\PackageRouteHelperCached */
	private $packageRouterHelper;
	/** @var \NetteAddons\Model\VendorRouteHelper */
	private $vendorRouteHelper;

	/**
	 * @param Model\PackageRouteHelperCached
	 * @param Model\VendorRouteHelper
	 */
	public function __construct(Model\PackageRouteHelperCached $packageRouterHelper, Model\VendorRouteHelper $vendorRouteHelper)
	{
		$this->packageRouterHelper = $packageRouterHelper;
		$this->vendorRouteHelper = $vendorRouteHelper;
	}

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new \Nette\Application\Routers\RouteList();

		// Setup router
		$router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		$router[] = new Route('packages.json', 'Api:Composer:packages'); // same as Packagist's route
		$router[] = new Route('downloads/<package>', array( // same as Packagist's route
			'module' => 'Api',
			'presenter' => 'Composer',
			'action' => 'downloadNotify',
			'package' => array(
				Route::PATTERN => '[^/]+/[^/]+',
			),
		));
		$router[] = new Route('api/github', 'Api:Github:postReceive'); // same as Packagist's route
		$router[] = new Route('page/<slug>[/<action>]', 'Page:default');
		$router[] = new Route('special/<action>[.<type=html (html|xml)>]', 'Special:default');
		$router[] = new Route('<id>[/<action>]', array(
			'presenter' => 'Detail',
			'action' => 'default',
			'id' => array(
				Route::PATTERN => '[^/]+/[^/]+',
				Route::FILTER_IN => $this->packageRouterHelper->filterIn,
				Route::FILTER_OUT => $this->packageRouterHelper->filterOut,
			)
		));
		$router[] = new Route('<vendor>', array(
			'presenter' => 'List',
			'action' => 'default',
			'vendor' => array(
				Route::FILTER_IN => $this->vendorRouteHelper->filterIn,
				Route::FILTER_OUT => $this->vendorRouteHelper->filterOut,
			),
		));
		$router[] = new Route('<presenter>[/<action>]', 'Homepage:default');

		return $router;
	}

}
