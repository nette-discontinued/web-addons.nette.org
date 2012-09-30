<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;



/**
 * Repository for addon votes.
 *
 * @author Jan Tvrdík
 */
class AddonVotes extends Table
{
	/** @var string */
	protected $tableName = 'addons_votes';



	/**
	 * Votes as given user for given addon with optional comment.
	 *
	 * @param  int addon id
	 * @param  int user id
	 * @param  int +1 or -1 or 0 (means cancel vote)
	 * @param  string optional comment
	 * @return void
	 */
	public function vote($addonId, $userId, $vote, $comment = NULL)
	{
		if (abs($vote) !== 1 && $vote !== 0) {
			throw new \NetteAddons\InvalidArgumentException('Vote can be only +1, -1 or 0.');
		}

		$now = new \DateTime('now');
		$this->connection->query('
			INSERT INTO ' . $this->tableName . '
			(`addonId`, `userId`, `vote`, `comment`, `datetime`)
			VALUES (?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE `vote` = ?, `datetime` = ?' ,
			$addonId, $userId, $vote, $comment, $now,
			$vote, $now
		);
	}



	/**
	 * Calculates addon popularity.
	 *
	 * @param  int addon id
	 * @return \stdClass
	 */
	public function calculatePopularity($addonId)
	{
		$minus = $this->findAll()->select('COUNT(*) AS c')
			->where('addonId', $addonId)
			->where('vote', -1)
			->fetch()->c;

		$plus = $this->findAll()->select('COUNT(*) AS c')
			->where('addonId', $addonId)
			->where('vote', 1)
			->fetch()->c;

		$total = $minus + $plus;
		$percent = ($total > 0 ? ($plus / $total) : 0.5) * 100;

		return (object) array(
			'plus' => $plus, // count of likes
			'minus' => $minus, // count of dislikes
			'percent' => $percent, // percent of likes
		);
	}
}
