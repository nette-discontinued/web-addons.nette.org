<?php

namespace NetteAddons;

use Nette\Http,
	NetteAddons\Model\Addon,
	NetteAddons\Model\Addons,
	NetteAddons\Model\AddonDownloads,
	NetteAddons\Model\AddonVersions,
	NetteAddons\Model\AddonVotes,
	Nette\Caching\IStorage,
	Nette\Caching\Cache;



/**
 * @author Jan Marek
 * @author Jan Tvrdík
 * @author Patrik Votoček
 * @author Michael Moravec
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

	/** @var AddonDownloads */
	private $addonDownloads;

	/** @var AddonVersions */
	private $addonVersions;

	/** @var AddonVotes */
	private $addonVotes;

	/** @var Forms\ReportFormFactory */
	private $reportForm;

	/** @var IStorage */
	private $cacheStorage;


	/**
	 * @param Addons
	 * @param AddonDownloads
	 * @param AddonVersions
	 * @param AddonVotes
	 */
	public function injectAddons(Addons $addons, AddonDownloads $downloads, AddonVersions $versions, AddonVotes $votes)
	{
		$this->addons = $addons;
		$this->addonDownloads = $downloads;
		$this->addonVersions = $versions;
		$this->addonVotes = $votes;
	}



	/**
	 * @param Forms\ReportFormFactory
	 */
	public function injectForms(Forms\ReportFormFactory $reportForm)
	{
		$this->reportForm = $reportForm;
	}



	/**
	 * @param IStorage
	 */
	public function injectCacheStorage(IStorage $storage)
	{
		$this->cacheStorage = $storage;
	}



	protected function startup()
	{
		parent::startup();

		if (!$row = $this->addons->find($this->id, $this->auth->isAllowed('addon', 'delete'))) {
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
	 * @author Jan Tvrdík
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
			$this->invalidateControl('rating');
		} else {
			$this->flashMessage('Voting was successful!');
			$this->redirect('this');
		}
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentReportForm()
	{
		$form = $this->reportForm->create($this->addon, $this->getUser()->getIdentity());

		$form->onSuccess[] = $this->reportFormSubmitted;

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
		$popularity = $this->addonVotes->calculatePopularity($this->addon->id);

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

		$this->template->plus = $popularity->plus;
		$this->template->minus = $popularity->minus;
		$this->template->percents = $popularity->percent;
		$this->template->myVote = $myVote;
		$this->template->usageStatistics = $usageStatistics;
	}
}
