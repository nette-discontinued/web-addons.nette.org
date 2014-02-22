<?php

namespace NetteAddons;

use Nette\Http;
use Nette\Caching\Cache;
use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonDownloads;


final class DetailPresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Addons
	 */
	public $addons;

	/**
	 * @inject
	 * @var \NetteAddons\Model\AddonDownloads
	 */
	public $addonDownloads;

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

		$this->template->content = $description['content'];
		$this->template->toc = $description['toc'];
		$this->template->netteRepositoryUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();
	}


	/**
	 * @param int addon ID
	 */
	public function renderVersions($id)
	{
	}


	/**
	 * @secured
	 * @param string version identifier
	 */
	public function handleDownload($version = NULL)
	{
		if ($version === NULL) { // current
			$version = $this->addonVersions->getCurrent($this->addon->versions);
		} elseif (isset($this->addon->versions[$version])) { // archive
			$version = $this->addon->versions[$version];
		} else {
			$this->error('Unknown addon version.');
		}

		$this->addonDownloads->saveDownload(
			AddonDownloads::TYPE_DOWNLOAD,
			$version->id,
			$this->getHttpRequest()->getRemoteAddress(),
			$this->getHttpRequest()->getHeader('user-agent'),
			$this->getUser()->isLoggedIn() ? $this->getUser()->getId() : NULL
		);

		$this->redirectUrl($version->distUrl);
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
		if ($this->isAjax()) {
			$this->redrawControl('rating');
		} else {
			$this->flashMessage('Voting was successful!');
			$this->redirect('this');
		}
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

		$addonId = $this->addon->id;
		$statsFrom = new \DateTime('- 7 days');
		$statsFrom->setTime(0, 0, 0);
		$statsTo = new \DateTime('yesterday');
		$statsTo->setTime(23, 59, 59);

		$statsCache = new Cache($this->cacheStorage, 'Addon.Detail.Stats');
		$key = sprintf('%d/%s_%s', $addonId, $statsFrom->format('Y-m-d'), $statsTo->format('Y-m-d'));

		$usageStatistics = $statsCache->load($key);
		if ($usageStatistics === NULL) {
			$usageStatistics = $this->addonDownloads->findDownloadUsage($addonId, $statsFrom, $statsTo);
			$statsCache->save($key, $usageStatistics, array(
				Cache::EXPIRE => '+ 1 day',
			));
		}

		$this['subMenu']->setAddon($this->addon);

		$this->template->addon = $this->addon;
		$this->template->version = $currentVersion;
		$this->template->composer = $currentVersion->composerJson;

		$this->template->myVote = $myVote;
		$this->template->usageStatistics = $usageStatistics;
		$this->template->showUsageStatistics = array_sum(array_map(function ($item) {
			return $item->count;
		}, $usageStatistics)) > 0;
	}
}
