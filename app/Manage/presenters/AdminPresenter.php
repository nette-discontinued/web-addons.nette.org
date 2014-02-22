<?php

namespace NetteAddons\Manage;

use Nette\Http\IResponse;
use NetteAddons\Forms\Form;
use NetteAddons\Model\Addons;
use NetteAddons\Model\AddonVotes;
use NetteAddons\Model\AddonReports;


final class AdminPresenter extends \NetteAddons\BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Addons
	 */
	public $addons;

	/**
	 * @inject
	 * @var \NetteAddons\Model\AddonVotes
	 */
	public $addonVotes;

	/**
	 * @inject
	 * @var \NetteAddons\Model\AddonReports
	 */
	public $reports;

	/**
	 * @inject
	 * @var \NetteAddons\Manage\Forms\ReportFormFactory
	 */
	public $reportForm;


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
			$this->error('This section is only for admins and moderators.', IResponse::S403_FORBIDDEN);
		}
	}


	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->addonVotes = array($this->addonVotes, 'calculatePopularity');
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
	 * @return \NetteAddons\Forms\Form
	 */
	protected function createComponentReportForm()
	{
		$form = $this->reportForm->create($this->getUser()->getIdentity());

		$form->onSuccess[] = array($this, 'reportFormSubmitted');

		return $form;
	}

	/**
	 * @param \NetteAddons\Forms\Form
	 */
	public function reportFormSubmitted(Form $form)
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

		$this['reportForm-report']->setValue($report->id);
	}


	/**
	 * @param int
	 */
	public function renderReport($id)
	{
		$this->template->report = $this->reports->find($id);
	}
}
