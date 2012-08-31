<?php

require_once __DIR__ . '/../libs/nette/nette/Nette/loader.php';
require_once __DIR__ . '/libs/HttpPHPUnit/init.php';
require_once __DIR__ . '/libs/Access/Init.php';

$http = new HttpPHPUnit\Main(__DIR__ . '/libs/PHPUnit');

require_once __DIR__ . '/boot.php';

$cvg = $http->coverage(__DIR__ . '/../app', __DIR__ . '/coverage');
$cvg->setProcessUncoveredFilesFromWhitelist(FALSE);

$http->run(__DIR__ . '/cases');
