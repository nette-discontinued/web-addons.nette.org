<?php

namespace NetteAddons\Model;

/**
 * For tests
 *
 * @author Jan Marek
 */
class DevelopmentUtils extends \Nette\Object
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
		$tables = $connection->getSupplementalDriver()->getTables();
		foreach ($tables as $table) {
			$connection->exec('SET foreign_key_checks = 0');
			$connection->exec("DROP TABLE `{$table['name']}`");
		}

		$this->executeFile(__DIR__ . '/db/schema.sql');
		$this->executeFile(__DIR__ . '/db/data.sql');
		$this->executeFile(__DIR__ . '/db/schema-triggers.sql');

		$this->cacheStorage->clean(array(\Nette\Caching\Cache::ALL => TRUE));
	}



	/**
	 * @param int
	 * @param int
	 */
	public function generateRandomDownloadsAndInstalls($maxCount = 5, $days = 5)
	{
		$this->db->beginTransaction();

		foreach ($this->db->table('addons_versions') as $version) {
			foreach ($this->db->table('users') as $user) {
				$limit = mt_rand(0, $maxCount);
				for ($i=0;$i<$limit;$i++) {
					$this->addDownloadOrInstall('download', $days, $version->id, $user->id);
				}
			}
			$limit = mt_rand(0, $maxCount);
			for ($i = 0; $i < $limit; $i++) {
				$this->addDownloadOrInstall('download', $days, $version->id);
			}
			$limit = mt_rand(0, $maxCount);
			for ($i = 0; $i < $limit; $i++) {
				$this->addDownloadOrInstall('install', $days, $version->id);
			}
		}

		$this->db->commit();
	}



	/**
	 * @param string
	 * @param int
	 * @param int
	 * @param int|NULL
	 */
	protected function addDownloadOrInstall($type = 'download', $days = 5, $versionId, $userId = NULL)
	{
		$datetime = \DateTime::createFromFormat('U', time() - ((int)(mt_rand(0, $days*24*60*60))));
		$ip = mt_rand(0, 254).'.'.mt_rand(0, 254).'.'.mt_rand(0, 254).'.'.mt_rand(0, 254);

		$this->db->table('addons_downloads')->insert(array(
			'versionId' => $versionId,
			'userId' => $userId,
			'ipAddress' => $ip,
			'userAgent' => 'RANDOM GENERATOR',
			'time' => $datetime,
			'type' => $type,
		));
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
