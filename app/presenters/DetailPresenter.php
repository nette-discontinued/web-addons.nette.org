<?php

namespace NetteAddons;

use Nette\Http;
use Nette\Caching\Cache;
use NetteAddons\Model\Addon;


final class DetailPresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Addons
	 */
	public $addons;

	/**
	 * @inject
	 * @var \NetteAddons\Model\AddonVersions
	 */
	public $addonVersions;

	/**
	 * @inject
	 * @var \NetteAddons\Model\AddonVotes
	 */
	public $addonVotes;

	/**
	 * @inject
	 * @var \NetteAddons\Forms\ReportFormFactory
	 */
	public $reportForm;

	/**
	 * @inject
	 * @var \Nette\Caching\IStorage
	 */
	public $cacheStorage;

	/**
	 * @inject
	 * @var \Nette\Database\Context
	 */
	public $db;

	/**
	 * @persistent
	 * @var int addon ID
	 */
	public $id;

	/** @var \NetteAddons\Model\Addon */
	private $addon;


	protected function startup()
	{
		parent::startup();

		if (!$row = $this->addons->find($this->id, $this->auth->isAllowed('addon', 'delete'))) {
			$this->error('Addon not found!');
		}

		$this->addon = Addon::fromActiveRow($row, $this->addonVotes);
		$this->addonVersions->rsort($this->addon->versions);
	}


	/**
	 * @param int addon ID
	 */
	public function renderDefault($id)
	{
		$description = $this->textPreprocessor->processDescription($this->addon);

		$owner = $this->db->table('users')->get($this->addon->userId);

		$this->template->content = $description['content'];
		$this->template->toc = $description['toc'];
		$this->template->netteRepositoryUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();
		$this->template->owner = $owner;
	}


	/**
	 * @param int addon ID
	 */
	public function renderVersions($id)
	{
		$owner = $this->db->table('users')->get($this->addon->userId);
		$this->template->owner = $owner;
	}


	/**
	 * Handle voting for current addon.
	 *
	 * @param  string 'up' or 'down'
	 * @return void
	 * @secured
	 */
	public function handleVote($vote)
	{
		$trans = array(
			'up' => 1,
			'cancel' => 0,
			'down' => -1,
		);

		if (!isset($trans[$vote])) {
			$this->error('Invalid vote.');
		} else {
			$vote = $trans[$vote];
		}

		if (!$this->user->loggedIn) {
			$this->error('Not logged in.', 403); // TODO: better
		}

		$this->addonVotes->vote($this->id, $this->user->id, $vote);
		$this->flashMessage('Voting was successful!');
		$this->redirect('this');
	}


	/**
	 * @return Forms\Form
	 */
	protected function createComponentReportForm()
	{
		$form = $this->reportForm->create($this->addon, $this->getUser()->getIdentity());

		$form->onSuccess[] = array($this, 'reportFormSubmitted');

		return $form;
	}


	/**
	 * @param Forms\Form
	 */
	public function reportFormSubmitted(Forms\Form $form)
	{
		if ($form->valid) {
			$this->flashMessage('Report sent.');
			$this->redirect('default');
		}
	}


	/**
	 * @param int
	 */
	public function renderReport($id)
	{

	}


	protected function beforeRender()
	{
		parent::beforeRender();

		$currentVersion = $this->addonVersions->getCurrent($this->addon->versions);

		if ($this->getUser()->isLoggedIn()) {
			$row = $this->addonVotes->findOneBy(array(
				'userId' => $this->getUser()->getId(),
				'addonId' => $this->addon->id,
			));
			$myVote = $row ? $row->vote : NULL;
		} else {
			$myVote = NULL;
		}

		$this['subMenu']->setAddon($this->addon);

		$this->template->addon = $this->addon;
		$this->template->version = $currentVersion;
		$this->template->composer = $currentVersion->composerJson;

		$this->template->myVote = $myVote;
	}
}
