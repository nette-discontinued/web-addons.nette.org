<?php

namespace NetteAddons\Forms;

use Nette\Security\IIdentity,
	NetteAddons\Model\Addon,
	NetteAddons\Model\AddonReports;


/**
 * @author  Patrik Votoček
 *
 * @property \NetteAddons\Model\Addon $addon
 * @property-write \Nette\Security\IIdentity $user
 */
class ReportForm extends BaseForm
{

	/** @var \NetteAddons\Model\AddonReports */
	private $reports;

	/** @var \NetteAddons\Model\Addon */
	private $addon;

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
	 * @return \NetteAddons\Model\Addon
	 */
	public function getAddon()
	{
		return $this->addon;
	}



	/**
	 * @param \NetteAddons\Model\Addon
	 * @return ReportForm
	 */
	public function setAddon(Addon $addon)
	{
		$this->addon = $addon;
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
		$this->addTextArea('message', 'Why / What?')->setRequired();

		$this->addSubmit('sub', 'Send');

		$this->onSuccess[] = $this->process;
	}



	public function process()
	{
		$this->reports->saveReport($this->user->getId(), $this->addon->id, $this['message']->value);
	}

}
