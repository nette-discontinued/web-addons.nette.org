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
		if (!$addon = $addons->find($id)) {
			$this->error('Addon not found!');
		}

		$popularity = $this->context->addonVotes->calculatePopularity($addon->id);
		$this->template->plus = $popularity->plus;
		$this->template->minus = $popularity->minus;
		$this->template->percents = $popularity->percent;

		$this->template->addon = $addon;
		$this->template->registerHelper('downloadlink', function ($version) use ($addons, $addon) {
			return $addons->getZipUrl($addon, $version);
		});

		$this->template->currentVersion = $this->context->addonVersions->findAddonCurrentVersion($addon);
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
