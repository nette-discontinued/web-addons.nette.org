<?php

require_once __DIR__ . '/../libs/autoload.php';
require_once __DIR__ . '/libs/HttpPHPUnit/init.php';
require_once __DIR__ . '/libs/Access/Init.php';

$http = new HttpPHPUnit\Main(__DIR__ . '/libs/PHPUnit');

require_once __DIR__ . '/bootstrap.php';

$cvg = $http->coverage(__DIR__ . '/../app', __DIR__ . '/coverage');
$cvg->setProcessUncoveredFilesFromWhitelist(FALSE);

$http->run(__DIR__ . '/cases');
