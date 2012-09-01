<?php

define('LIBS_DIR', __DIR__ . '/libs');

use Nette\Diagnostics\Debugger as Debug;
use Nette\Config\Configurator;
use Nette\Loaders\RobotLoader;

$configurator = new Configurator();
$configurator->setDebugMode();
$configurator->enableDebugger(__DIR__ . '/temp/log');
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->createRobotLoader()
	->addDirectory(LIBS_DIR)
	->addDirectory(__DIR__ . '/cases')
	->addDirectory(__DIR__ . '/../app')
	->register();
