<?php

namespace NetteAddons;

use Nette\Application\Routers\Route;
use Nette\Config\Configurator;
use NetteAddons\Model\Addon;

require_once LIBS_DIR . '/autoload.php';



$configurator = new Configurator;

// Enable Debugger
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->register();

// Create Dependency Injection container
$configurator->addConfig(__DIR__ . '/config/config.neon', Configurator::NONE);
if (file_exists($config = __DIR__ . '/config/config.local.neon')) {
	$configurator->addConfig($config, Configurator::NONE);
}
$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$container->router[] = new Route('packages.json', 'Api:Composer:packages'); // same as Packagist's route
$container->router[] = new Route('downloads/<package>', array( // same as Packagist's route
	'module' => 'Api',
	'presenter' => 'Composer',
	'action' => 'downloadNotify',
	'package' => array(
		Route::PATTERN => '[^/]+/[^/]+',
	),
));
$container->router[] = new Route('api/github', 'Api:Github:postReceive'); // same as Packagist's route
$container->router[] = new Route('page/<slug>[/<action>]', 'Page:default');
$container->router[] = new Route('special/<action>[.<type=html (html|xml)>]', 'Special:default');
$composerPackageRouteHelper = $container->packageRouterHelper;
$container->router[] = new Route('<id>[/<action>]', array(
	'presenter' => 'Detail',
	'action' => 'default',
	'id' => array(
		Route::PATTERN => '[^/]+/[^/]+',
		Route::FILTER_IN => $composerPackageRouteHelper->filterIn,
		Route::FILTER_OUT => $composerPackageRouteHelper->filterOut,
	)
));
$composerVendorRouteHelper = $container->vendorRouteHelper;
$container->router[] = new Route('<vendor>', array(
	'presenter' => 'List',
	'action' => 'byVendor',
	'vendor' => array(
		Route::FILTER_IN => $composerVendorRouteHelper->filterIn,
		Route::FILTER_OUT => $composerVendorRouteHelper->filterOut,
	),
));
$container->router[] = new Route('<presenter>[/<action>]', 'Homepage:default');

// Run the application!
if (!$container->parameters['consoleMode']) {
	$container->application->run();
}
