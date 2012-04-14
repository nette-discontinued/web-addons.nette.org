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
	protected $tableName = 'addon_votes';

	/**
	 * Votes as given user for given addon with optional comment.
	 *
	 * @param  int addon id
	 * @param  int user id
	 * @param  int +1 or -1
	 * @param  string optional comment
	 * @return void
	 */
	public function vote($addonId, $userId, $vote, $comment = NULL)
	{
		$this->connection->query('
			INSERT INTO `addon_votes`
			(`addon_id`, `user_id`, `vote`, `comment`)
			VALUES (?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE `vote` = ?',
			$addonId, $userId, $vote, $comment,
			$vote
		);
	}

}
