<?php

require_once __DIR__ . '/bootstrap.php';

require_once LIBS_DIR . '/HttpPHPUnit/init.php';

$http = new HttpPHPUnit\Main(LIBS_DIR . '/PHPUnit');

$cvg = $http->coverage(__DIR__ . '/../app', __DIR__ . '/coverage');
$cvg->setProcessUncoveredFilesFromWhitelist(FALSE);

$http->run(__DIR__ . '/cases');
