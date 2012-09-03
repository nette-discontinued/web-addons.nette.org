<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route;
use Nette\Config\Configurator;

// Load Nette Framework
require LIBS_DIR . '/autoload.php';
require APP_DIR . '/misc/functions.php';


// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode(TRUE);
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon', Configurator::NONE);
$configurator->addConfig(__DIR__ . '/config/config.local.neon', Configurator::NONE);
$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$container->router[] = new Route('packages.json', 'Packages:default');
$container->router[] = new Route('<presenter>[/<action>][/<id>]', array(
	'presenter' => 'Homepage',
	'action' => 'default'
));


// Configure and run the application!
if (!$container->parameters['consoleMode']) {
	$container->application->run();
}
