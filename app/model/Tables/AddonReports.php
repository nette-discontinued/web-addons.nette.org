<?php

namespace NetteAddons\Model;


class AddonReports extends Table
{
	/** @var string */
	protected $tableName = 'addons_reports';


	/**
	 * @param int
	 * @param int
	 * @param string
	 * @param string|NULL
	 * @param int|NULL
	 * @return \Nette\Database\Table\ActiveRow
	 * @throws \InvalidArgumentException
	 */
	public function saveReport($userId, $addonId, $message, $reason = NULL, $zappedBy = NULL)
	{
		return $this->createRow(array(
			'userId' => $userId,
			'addonId' => $addonId,
			'reportedAt' => new \DateTime,
			'message' => $message,
			'reason' => $reason,
			'zappedBy' => $zappedBy,
		));
	}


	/**
	 * @param int
	 * @param string|NULL
	 * @param int|NULL
	 * @return int|NULL
	 */
	public function updateReport($id, $reason = NULL, $zappedBy = NULL)
	{
		$row = $this->find($id);

		if (!$row) {
			return NULL;
		}

		return $row->update(array('reason' => $reason, 'zappedBy' => $zappedBy));
	}
}
