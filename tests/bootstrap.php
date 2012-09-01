<?php

require_once __DIR__ . '/../libs/autoload.php';

define('LIBS_DIR', __DIR__ . '/libs');

require_once LIBS_DIR . '/Access/Init.php';

use Nette\Config\Configurator;

$configurator = new Configurator();
$configurator->setDebugMode();
$configurator->enableDebugger(__DIR__ . '/temp/log');
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->createRobotLoader()
	->addDirectory(LIBS_DIR)
	->addDirectory(__DIR__ . '/cases')
	->addDirectory(__DIR__ . '/../app')
	->register();
