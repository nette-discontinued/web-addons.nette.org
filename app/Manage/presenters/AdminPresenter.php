<?php

namespace NetteAddons\Manage;

use NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVotes,
	NetteAddons\Model\AddonReports;


/**
 * @author Patrik Votoček
 */
final class AdminPresenter extends \NetteAddons\BasePresenter
{
	/** @var \NetteAddons\Model\Addons */
	private $addons;

	/** @var \NetteAddons\Model\AddonVotes */
	private $addonVotes;

	/** @var \NetteAddons\Model\AddonReports */
	private $reports;

	/** @var Forms\ReportForm */
	private $reportForm;



	/**
	 * @param \NetteAddons\Model\Addons
	 * @param \NetteAddons\Model\AddonVotes
	 * @param \NetteAddons\Model\AddonReports
	 */
	public function injectModel(Addons $addons, AddonVotes $addonVotes, AddonReports $report)
	{
		$this->addons = $addons;
		$this->addonVotes = $addonVotes;
		$this->reports = $report;
	}



	/**
	 * @param Forms\ReportForm
	 */
	public function injectForms(Forms\ReportForm $reportForm)
	{
		$this->reportForm = $reportForm;
	}



	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection
	 */
	public function checkRequirements($element)
	{
		$user = $this->getUser();
		if (!$user->isLoggedIn()) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect(':Sign:in', $this->storeRequest());
		} elseif (!$this->user->isInRole('moderators') && !$this->user->isInRole('administrators')) {
			$this->error('This section is only for admins and moderators.', 403);
		}
	}



	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->addonVotes = callback($this->addonVotes, 'calculatePopularity');
	}



	public function actionDeleted()
	{
		if (!$this->auth->isAllowed('addon', 'delete')) {
			$this->error('You are not allowed to list deleted addons.', 403);
		}
	}



	public function renderDeleted()
	{
		$this->template->addons = $this->addons->findDeleted();
	}



	public function renderReports()
	{
		$this->template->reports = $this->reports->findAll()->order('reportedAt DESC');
	}



	/**
	 * @return Forms\ReportForm
	 */
	protected function createComponentReportForm()
	{
		$form = $this->reportForm;

		$form->setUser($this->getUser()->identity);

		$form->onSuccess[] = $this->reportFormSubmitted;

		return $form;
	}



	/**
	 * @param Forms\ReportForm
	 */
	public function reportFormSubmitted(Forms\ReportForm $form)
	{
		if ($form->valid) {
			$this->flashMessage('Report zapped.');
			$this->redirect('reports');
		}
	}



	/**
	 * @param int
	 */
	public function actionReport($id)
	{
		$report = $this->reports->find($id);
		if (!$report) {
			$this->error('Report not found.');
		}

		$this['reportForm']->setReport($report->id);
	}



	/**
	 * @param int
	 */
	public function renderReport($id)
	{
		$this->template->report = $this->reports->find($id);
	}

}
