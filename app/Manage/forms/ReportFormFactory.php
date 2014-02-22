<?php

namespace NetteAddons\Manage\Forms;

use Nette\Security\IIdentity,
	NetteAddons\Forms\Form,
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
	 * @param IIdentity
	 * @return Form
	 */
	public function create(IIdentity $user)
	{
		$form = new Form;

		$form->addHidden('report')
			->setRequired();
		$form->addTextArea('reason', 'What?')
			->setRequired();

		$form->addSubmit('sub', 'Zap');

		$model = $this->reports;
		$form->onSuccess[] = function(Form $form) use($model, $user) {
			$values = $form->getValues();

			$model->updateReport($values->report, $values->reason, $user->getId());
		};

		return $form;
	}

}
