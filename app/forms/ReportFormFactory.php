<?php

namespace NetteAddons\Forms;

use Nette\Security\IIdentity,
	NetteAddons\Model\Addon,
	NetteAddons\Model\AddonReports;


/**
 * @author  Patrik Votoček
 */
class ReportFormFactory extends \Nette\Object
{

	/** @var AddonReports */
	private $reports;


	/**
	 * @param AddonReports
	 */
	public function __construct(AddonReports $reports)
	{
		$this->reports = $reports;
	}


	/**
	 * @param Addon
	 * @param IIdentity
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
			$model->saveReport($user->getId(), $addon->id, $values['message']);
		};

		return $form;
	}

}
