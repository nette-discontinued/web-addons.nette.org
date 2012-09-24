<?php

namespace NetteAddons;

use NetteAddons\Model\Addon;
use NetteAddons\Model\Addons;
use NetteAddons\Model\AddonVersions;
use NetteAddons\Model\AddonVotes;
use Nette\Utils\Json;



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

	/** @var TexyFactory */
	private $texyFactory;



	public function injectAddons(Addons $addons)
	{
		$this->addons = $addons;
	}



	public function injectAddonVersions(AddonVersions $addonVersions)
	{
		$this->addonVersions = $addonVersions;
	}



	public function injectAddonVotes(AddonVotes $addonVotes)
	{
		$this->addonVotes = $addonVotes;
	}



	public function injectTexyFactory(TexyFactory $factory)
	{
		$this->texyFactory = $factory;
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
		$texy = $this->texyFactory->create();

		$this->template->addon = $this->addon;
		$this->template->version = $currentVersion;
		$this->template->composer = $currentVersion->composerJson;

		$this->template->content = $texy->process($this->addon->description);
		$this->template->toc = $texy->headingModule->TOC;

		$this->template->plus = $popularity->plus;
		$this->template->minus = $popularity->minus;
		$this->template->percents = $popularity->percent;
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
