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
$container->router[] = new Route('downloads/<package>', 'Api:Composer:downloadNotify'); // same as Packagist's route
$container->router[] = new Route('api/github', 'Api:Github:postReceive'); // same as Packagist's route
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
$container->router[] = new Route('<vendor>', array(
	'presenter' => 'List',
	'action' => 'byVendor',
	'vendor' => array(
		Route::FILTER_IN => function ($vendor) use ($container) {
			$row = $container->addons->findByComposerVendor($vendor)->limit(1)->fetch();
			if (!$row) return NULL;
			$addon = Addon::fromActiveRow($row);
			return $addon->composerVendor;
		},
		Route::FILTER_OUT => function ($vendor) use ($container) {
			$row = $container->addons->findByComposerVendor($vendor)->limit(1)->fetch();
			if (!$row) return NULL;
			$addon = Addon::fromActiveRow($row);
			return $addon->composerVendor;
		},
	),
));
$container->router[] = new Route('<presenter>[/<action>]', 'Homepage:default');

// Run the application!
if (!$container->parameters['consoleMode']) {
	$container->application->run();
}
