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


	/**
	 * @param $id
	 */
	public function renderDefault($id)
	{
		$addons = $this->context->addons;
		$addon = $addons->find($id);

		if (!$addon) {
			$this->error('Addon not found!');
		}

		$votesMinus = $this->context->addonVotes->findAll()->
								select('COUNT(*) AS c')->
								where(array('addon_id'=> $addon->id ,
											'vote' => -1))->fetch()->c;

		$votesPlus = $this->context->addonVotes->findAll()->
								select('COUNT(*) AS c')->
								where(array('addon_id'=> $addon->id ,
											'vote'=> 1))->fetch()->c;


		if (($votesPlus + $votesMinus) > 0){
			$percents =  $votesPlus / ( $votesMinus + $votesPlus ) * 100;
		} else {
			$percents = 50;
		}


		$this->template->plus = $votesPlus;
		$this->template->minus = $votesMinus;
		$this->template->percents = (int) $percents;

		$this->template->addon = $addon;
		$this->template->registerHelper('downloadlink', function ($version) use ($addons, $addon) {
			return $addons->getZipUrl($addon, $version);
		});
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
		$trans = array(
			'up' => 1,
			'cancel' => 0,
			'down' => -1,
		);

		if (!isset($trans[$vote])) {
			$this->error('invalid vote');
		} else {
			$vote = $trans[$vote];
		}

		if (!$this->user->loggedIn) {
			$this->error('not logged in', 403); // TODO: better
		}

		$this->context->addonVotes->vote($this->id, $this->user->id, $vote);
		$this->flashMessage('Voting was successfull!');
		$this->redirect('this');
	}

}
