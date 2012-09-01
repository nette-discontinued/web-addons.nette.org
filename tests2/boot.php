<?php

define('LIBS_DIR', __DIR__ . '/libs');

use Nette\Diagnostics\Debugger as Debug;
use Nette\Config\Configurator;
use Nette\Loaders\RobotLoader;

$configurator = new Configurator();
$configurator->enableDebugger();
$configurator->setTempDirectory(__DIR__ . '/tmp');
$configurator->createRobotLoader()
	->addDirectory(LIBS_DIR)
	->addDirectory(__DIR__ . '/cases')
	->addDirectory(__DIR__ . '/../app')
	->register();
