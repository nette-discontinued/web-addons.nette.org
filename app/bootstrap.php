<?php

namespace NetteAddons;

use Nette\Application\Routers\Route;
use Nette\Config\Configurator;

require_once LIBS_DIR . '/autoload.php';



$configurator = new Configurator;

// Enable Debugger
if (getenv('DEBUG_MODE') == 'on') {
	$configurator->setDebugMode(TRUE);
} elseif (getenv('DEBUG_MODE') == 'off') {
	$configurator->setDebugMode(FALSE);
}
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
} elseif (file_exists($config = __DIR__ . '/config/config.local.php')) {
	$configurator->addConfig($config, Configurator::NONE);
}
$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$container->router[] = new Route('packages.json', 'Packages:default');
$container->router[] = new Route('<presenter>[/<action>]', 'Homepage:default');

// Run the application!
if (!$container->parameters['consoleMode']) {
	$container->application->run();
}
