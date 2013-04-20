<?php

namespace NetteAddons\Manage;

use NetteAddons\Forms\Form,
	NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVotes,
	NetteAddons\Model\AddonReports;


/**
 * @author Patrik Votoček
 */
final class AdminPresenter extends \NetteAddons\BasePresenter
{
	/**
	 * @var \NetteAddons\Model\Addons
	 * @inject
	 */
	public $addons;

	/**
	 * @var \NetteAddons\Model\AddonVotes
	 * @inject
	 */
	public $addonVotes;

	/**
	 * @var \NetteAddons\Model\AddonReports
	 * @inject
	 */
	public $reports;

	/**
	 * @var Forms\ReportFormFactory
	 * @inject
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
	 * @return Form
	 */
	protected function createComponentReportForm()
	{
		$form = $this->reportForm->create($this->getUser()->getIdentity());

		$form->onSuccess[] = $this->reportFormSubmitted;

		return $form;
	}



	/**
	 * @param Form
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
