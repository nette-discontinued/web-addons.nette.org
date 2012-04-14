<?php

namespace NetteAddons;

/**
 * @author Jan Marek
 */
class DetailPresenter extends BasePresenter
{
	/**
	 * @var int addon ID
	 * @persistent
	 */
	public $id;

	public function renderDefault($id)
	{
		$addon = $this->context->addons->find($id);
		if (!$addon) $this->error('Addon not found!');
		$this->template->addon = $addon;
	}

	/**
	 * Handle voting for current addon.
	 *
	 * @author Jan Tvrdík
	 * @param  string 'up' or 'down'
	 * @return void
	 */
	public function handleVote($vote)
	{
		if ($vote === 'up') {
			$vote = 1;
		} elseif ($vote === 'down') {
			$vote = -1;
		} else {
			$this->error('invalid vote');
		}

		if (!$this->user->loggedIn) {
			$this->error('not logged in', 403); // TODO: better
		}

		$this->context->addonVotes->vote($this->user->id, $this->id, $vote);
		$this->flashMessage('Voting was successfull!');
		$this->redirect('this');
	}

}
