<?php

namespace NetteAddons\Manage\Forms;

use Nette\Security\IIdentity,
	NetteAddons\Model\AddonReports;


/**
 * @author  Patrik Votoček
 *
 * @property int $report
 * @property-write \Nette\Security\IIdentity $user
 */
class ReportForm extends \NetteAddons\Forms\BaseForm
{
	/** @var int */
	private $report;

	/** @var \NetteAddons\Model\AddonReports */
	private $reports;

	/** @var \Nette\Security\IIdentity */
	private $user;


	/**
	 * @param \NetteAddons\Model\AddonReports
	 */
	public function __construct(AddonReports $reports)
	{
		$this->reports = $reports;
		parent::__construct();
	}



	/**
	 * @return int
	 */
	public function getReport()
	{
		return $this->report;
	}



	/**
	 * @param int
	 * @return ReportForm
	 */
	public function setReport($report)
	{
		$this->report = $report;
		return $this;
	}



	/**
	 * @param \Nette\Security\IIdentity
	 * @return ReportForm
	 */
	public function setUser(IIdentity $user)
	{
		$this->user = $user;
		return $this;
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function buildForm()
	{
		$this->addTextArea('reason', 'What?')->setRequired();

		$this->addSubmit('sub', 'Zap');

		$this->onSuccess[] = $this->process;
	}



	public function process()
	{
		$this->reports->updateReport($this->report, $this['reason']->value, $this->user->getId());
	}

}
