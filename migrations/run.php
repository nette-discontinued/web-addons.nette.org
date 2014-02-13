<?php
/** @var SystemContainer $dic */
$dic = require __DIR__ . '/../app/bootstrap.php';

/** @var Nette\Database\Context $ndb */
$ndb = $dic->getByType('Nette\Database\Context');

$driver = new Nextras\Migrations\Drivers\MySqlNetteDbDriver($ndb, 'migrations');

if (PHP_SAPI === 'cli') {
	$controller = new Nextras\Migrations\Controllers\ConsoleController($driver);
} else {
	$controller = new Nextras\Migrations\Controllers\HttpController($driver);
}

$controller->addGroup('structures', __DIR__ . '/structures');
$controller->addGroup('test-data', __DIR__ . '/test-data', ['structures']);
$controller->addExtension('sql', new Nextras\Migrations\Extensions\NetteDbSql($ndb));
$controller->run();

