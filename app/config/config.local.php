<?php
return array(
	'parameters' => array(
		'database' => array(
			'driver' => 'mysql',
			'host' => getenv('MYSQL_DB_HOST'),
			'dbname' => getenv('MYSQL_DB_NAME'),
			'user' => getenv('MYSQL_USERNAME'),
			'password' => getenv('MYSQL_PASSWORD'),
		),
	),
);