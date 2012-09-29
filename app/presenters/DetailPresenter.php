<?php

namespace NetteAddons;

use NetteAddons\Model\Addon;
use NetteAddons\Model\Addons;
use NetteAddons\Model\AddonVersions;
use NetteAddons\Model\AddonVotes;



/**
 * @author Jan Marek
 * @author Jan Tvrdík
 */
class DetailPresenter extends BasePresenter
{
	/**
	 * @var int addon ID
	 * @persistent
	 */
	public $id;

	/** @var Addon */
	private $addon;

	/** @var Addons */
	private $addons;

	/** @var AddonVersions */
	private $addonVersions;

	/** @var AddonVotes */
	private $addonVotes;

	/** @var TextPreprocessor */
	private $textPreprocessor;



	public function injectAddons(Addons $addons, AddonVersions $addonVersions, AddonVotes $addonVotes)
	{
		$this->addons = $addons;
		$this->addonVersions = $addonVersions;
		$this->addonVotes = $addonVotes;
	}



	public function injectTextPreprocessor(TextPreprocessor $factory)
	{
		$this->textPreprocessor = $factory;
	}



	protected function startup()
	{
		parent::startup();

		if (!$row = $this->addons->find($this->id)) {
			$this->error('Addon not found!');
		}
		$this->addon = Addon::fromActiveRow($row);
		$this->addonVersions->rsort($this->addon->versions);
	}



	/**
	 * @param int addon ID
	 */
	public function renderDefault($id)
	{
		$currentVersion = $this->addonVersions->getCurrent($this->addon->versions);
		$popularity = $this->addonVotes->calculatePopularity($this->addon->id);
		if ($this->getUser()->isLoggedIn()) {
			$row = $this->addonVotes->findOneBy(array('userId' => $this->getUser()->getId()));
			$myVote = $row ? $row->vote : NULL;
		} else {
			$myVote = NULL;
		}
		$description = $this->textPreprocessor->processDescription($this->addon);

		$this->template->addon = $this->addon;
		$this->template->version = $currentVersion;
		$this->template->composer = $currentVersion->composerJson;

		$this->template->content = $description['content'];
		$this->template->toc = $description['toc'];

		$this->template->plus = $popularity->plus;
		$this->template->minus = $popularity->minus;
		$this->template->percents = $popularity->percent;
		$this->template->myVote = $myVote;
	}



	/**
	 * @param int addon ID
	 */
	public function renderArchive($id)
	{
		$this->template->addon = $this->addon;
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

		$this->addonVotes->vote($this->id, $this->user->id, $vote);
		$this->flashMessage('Voting was successfull!');
		$this->redirect('this');
	}
}
