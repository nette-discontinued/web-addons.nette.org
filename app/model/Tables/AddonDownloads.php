<?php

namespace NetteAddons\Model;

use Nette;

/**
 * @author Michael Moravec
 */
class AddonDownloads extends Table
{
	const TYPE_DOWNLOAD = 'download',
		TYPE_INSTALL = 'install';

	protected $tableName = 'addons_downloads';



	/**
	 * @param string
	 * @param int
	 * @param string
	 * @param string
	 * @param int|NULL
	 * @return \Nette\Database\Table\ActiveRow
	 * @throws \InvalidArgumentException
	 */
	public function saveDownload($type, $versionId, $ipAddress, $userAgent, $userId = NULL)
	{
		if (!$this->isTypeValid($type)) {
			throw new \InvalidArgumentException('Invalid download type given.');
		}

		return $this->createRow(array(
			'versionId' => $versionId,
			'ipAddress' => $ipAddress,
			'userAgent' => $userAgent,
			'userId'    => $userId,
			'time'       => new \DateTime(),
			'type'       => $type,
		));
	}

	/**
	 * Get cumulative download statistics for an addon in a date range
	 *
	 * @param int
	 * @param \DateTime
	 * @param \DateTime
	 * @return array day => { day, count }
	 */
	public function findDownloadUsage($addonId, \DateTime $from, \DateTime $to)
	{
		$result = $this->findAll()
			->select('DATE(time) day, COUNT(*) c')
			->where('versionId.addonId = ? AND fake = 0 AND time BETWEEN ? AND ?', $addonId, $from, $to)
			->group('day');

		$stats = array();
		for ($day = clone $from; $day <= $to; $day->modify('+ 1 days')) {
			$stats[$day->format('Y-m-d')] = (object) array(
				'day' => clone $day,
				'count' => 0,
			);
		}

		foreach ($result as $row) {
			$stats[$row->day->format('Y-m-d')]->count = $row->c;
		}

		return $stats;
	}



	/**
	 * @param string
	 * @return bool
	 */
	public function isTypeValid($type)
	{
		return in_array($type, array(self::TYPE_DOWNLOAD, self::TYPE_INSTALL), TRUE);
	}

}
