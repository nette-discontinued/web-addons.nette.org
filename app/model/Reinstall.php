<?php

namespace NetteAddons\Model;

/**
 * For tests
 *
 * @author Jan Marek
 */
class Reinstall extends \Nette\Object
{

	private $db;



	public function __construct(\Nette\Database\Connection $db)
	{
		$this->db = $db;
	}



	public function recreateDatabase()
	{
		$connection = $this->db;

		//$connection->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);

		$tables = $connection->getSupplementalDriver()->getTables();
		foreach ($tables as $table) {
			$connection->exec('SET foreign_key_checks = 0');
			$connection->exec("DROP TABLE `{$table['name']}`");
		}

		\Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/db/current-schema.sql");
		\Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/db/data.sql");
	}

}
