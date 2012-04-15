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



	public function reinstall()
	{
		$connection = $this->db;

		//$connection->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);

		$tables = $connection->getSupplementalDriver()->getTables();
		foreach ($tables as $table) {
			$connection->exec('SET foreign_key_checks = 0');
			$connection->exec("DROP TABLE `{$table['name']}`");
		}

		\Nette\Database\Helpers::loadFromFile($connection, APP_DIR . "/model/db/schema.sql");
		\Nette\Database\Helpers::loadFromFile($connection, APP_DIR . "/model/db/data.sql");
	}

}
