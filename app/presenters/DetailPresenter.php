<?php

namespace NetteAddons;

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



	/**
	 * @param int addon ID
	 */
	public function renderDefault($id)
	{
		if (!$addon = $this->addons->find($id)) {
			$this->error('Addon not found!');
		}

		$texy = $this->texyFactory->create();
		$this->template->content = $texy->process($addon->description);
		$this->template->toc = $texy->headingModule->TOC;

		$popularity = $this->addonVotes->calculatePopularity($addon->id);
		$currentVersion = $this->addonVersions->findAddonCurrentVersion($addon);
		$versionRow = $addon->related('versions')->where('version', $currentVersion)->fetch();

		$this->template->plus = $popularity->plus;
		$this->template->minus = $popularity->minus;
		$this->template->percents = $popularity->percent;

		$this->template->addon = $addon;
		$this->template->version = $versionRow;
		$this->template->composer = $versionRow->composerJson ? Json::decode($versionRow->composerJson) : NULL;
	}



	/**
	 * @param int addon ID
	 */
	public function renderArchive($id)
	{
		if (!$addon = $this->addons->find($id)) {
			$this->error('Addon not found!');
		}

		$this->template->addon = $addon;
		$this->template->versions = $addon->related('versions');
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
