<?php

namespace NetteAddons\Forms;

use Nette\Security\IIdentity;
use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonReports;


class ReportFormFactory extends \Nette\Object
{
	/** @var \NetteAddons\Model\AddonReports */
	private $reports;


	public function __construct(AddonReports $reports)
	{
		$this->reports = $reports;
	}


	/**
	 * @param \NetteAddons\Model\Addon
	 * @param \Nette\Security\IIdentity
	 * @return Form
	 */
	public function create(Addon $addon, IIdentity $user)
	{
		$form = new Form;

		$form->addTextArea('message', 'Why / What?')
			->setRequired();

		$form->addSubmit('sub', 'Send');

		$model = $this->reports;
		$form->onSuccess[] = function(Form $form) use($model, $addon, $user) {
			$values = $form->getValues();
			$model->saveReport($user->getId(), $addon->id, $values->message);
		};

		return $form;
	}
}
