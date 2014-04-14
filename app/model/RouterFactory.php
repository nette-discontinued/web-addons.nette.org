<?php

namespace NetteAddons;

use Nette\Application\Routers\CliRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


class RouterFactory extends \Nette\Object
{
	/** @var \NetteAddons\Model\PackageRouteHelperCached */
	private $packageRouterHelper;
	/** @var \NetteAddons\Model\VendorRouteHelper */
	private $vendorRouteHelper;


	public function __construct(
		Model\PackageRouteHelperCached $packageRouterHelper,
		Model\VendorRouteHelper $vendorRouteHelper
	) {
		$this->packageRouterHelper = $packageRouterHelper;
		$this->vendorRouteHelper = $vendorRouteHelper;
	}


	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();

		// CLI
		$router[] = new CliRouter(array(
			'action' => 'Cli:Help:default',
		));

		// Setup router
		$router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		$router[] = new Route('api/github', 'Api:Github:postReceive'); // same as Packagist's route
		$router[] = new Route('page/<slug ([a-z][a-z0-9.-]*(?:/[a-z][a-z0-9.-]*)?)>', 'Page:default');
		$router[] = new Route('special/<action>[.<type=html (html|xml)>]', 'Special:default');
		$router[] = new Route('<id>[/<action>]', array(
			'presenter' => 'Detail',
			'action' => 'default',
			'id' => array(
				Route::PATTERN => '[^/]+/[^/]+',
				Route::FILTER_IN => array($this->packageRouterHelper, 'filterIn'),
				Route::FILTER_OUT => array($this->packageRouterHelper, 'filterOut'),
			)
		));
		$router[] = new Route('<vendor>', array(
			'presenter' => 'List',
			'action' => 'default',
			'vendor' => array(
				Route::FILTER_IN => array($this->vendorRouteHelper, 'filterIn'),
				Route::FILTER_OUT => array($this->vendorRouteHelper, 'filterOut'),
			),
		));
		$router[] = new Route('<? cs|en>', 'Homepage:default', Route::ONE_WAY);
		$router[] = new OldAddonsRoute(array(
			'presenter' => 'Detail',
			'action' => 'default',
		));
		$router[] = new Route('<presenter>[/<action>]', 'Homepage:default');

		return $router;
	}
}
