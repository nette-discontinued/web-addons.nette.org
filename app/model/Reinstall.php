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

	private $cacheStorage;


	public function __construct(\Nette\Database\Connection $db, \Nette\Caching\IStorage $cacheStorage)
	{
		$this->db = $db;
		$this->cacheStorage = $cacheStorage;
	}



	public function recreateDatabase()
	{
		$connection = $this->db;

		//$connection->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);

		$tables = $connection->getSupplementalDriver()->getTables();
		foreach ($tables as $table) {
			$connection->exec('SET foreign_key_checks = 0');
			$sql = /*$table['view'] HACK: Nette bug #792 */ $table['name'] === 'users_view' ? "DROP VIEW `{$table['name']}`" : "DROP TABLE `{$table['name']}`";
			$connection->exec($sql);
		}

		$this->executeFile(__DIR__ . '/db/current-schema.sql');
		$this->executeFile(__DIR__ . '/db/data.sql');

		$this->cacheStorage->clean(array(\Nette\Caching\Cache::ALL => TRUE));
	}



	/**
	 * Import taken from Adminer, slightly modified
	 * Note: This implementation is aware of delimiters used for trigger definitions (unlike Nette\Database)
	 *
	 * @author   Jakub Vrána, Jan Tvrdík, Michael Moravec
	 * @license  Apache License
	 */
	private function executeFile($file)
	{
		$query = file_get_contents($file);

		$delimiter = ';';
		$offset = 0;
		while ($query != '') {
			if (!$offset && preg_match('~^\\s*DELIMITER\\s+(.+)~i', $query, $match)) {
				$delimiter = $match[1];
				$query = substr($query, strlen($match[0]));
			} else {
				preg_match('(' . preg_quote($delimiter) . '|[\'`"]|/\\*|-- |#|$)', $query, $match, PREG_OFFSET_CAPTURE, $offset); // should always match
				$found = $match[0][0];
				$offset = $match[0][1] + strlen($found);

				if (!$found && rtrim($query) === '') {
					break;
				}

				if (!$found || $found == $delimiter) { // end of a query
					$q = substr($query, 0, $match[0][1]);

					$this->db->exec($q);

					$query = substr($query, $offset);
					$offset = 0;
				} else { // find matching quote or comment end
					while (preg_match('~' . ($found == '/*' ? '\\*/' : (preg_match('~-- |#~', $found) ? "\n" : "$found|\\\\.")) . '|$~s', $query, $match, PREG_OFFSET_CAPTURE, $offset)) { //! respect sql_mode NO_BACKSLASH_ESCAPES
						$s = $match[0][0];
						$offset = $match[0][1] + strlen($s);
						if ($s[0] !== '\\') {
							break;
						}
					}
				}
			}
		}
	}
}
