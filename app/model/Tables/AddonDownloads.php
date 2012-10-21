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
	 * @param string
	 * @return bool
	 */
	public function isTypeValid($type)
	{
		return in_array($type, array(self::TYPE_DOWNLOAD, self::TYPE_INSTALL), TRUE);
	}

}
