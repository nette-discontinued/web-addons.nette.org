<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
if (PHP_SAPI !== 'cli' && file_exists(__DIR__ . '/../maintenance')) {
	require __DIR__ . '/.maintenance.php';
}

// Let bootstrap create Dependency Injection container.
$container = require __DIR__ . '/../app/bootstrap.php';

// Run application.
$container->getService('application')->run();
