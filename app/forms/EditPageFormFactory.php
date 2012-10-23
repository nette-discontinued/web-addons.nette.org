<?php

namespace NetteAddons\Forms;

use Nette\Security\IIdentity,
	Nette\Database\Table\ActiveRow,
	NetteAddons\Model\Pages;


/**
 * @author  Patrik Votoček
 */
class EditPageFormFactory extends \Nette\Object
{

	/** @var Pages */
	private $pages;


	/**
	 * @param Pages
	 */
	public function __construct(Pages $pages)
	{
		$this->pages = $pages;
	}


	/**
	 * @param ActiveRow
	 * @param IIdentity
	 * @return Form
	 */
	public function create(ActiveRow $page, IIdentity $user)
	{
		$form = new Form;
		$form->addText('name', 'Name')
			->setRequired()
			->setAttribute('class', 'text input-half');
		$form->addTextArea('content', 'Content')
			->setRequired()
			->setAttribute('class', 'fullscreenable');

		$form->addSubmit('sub', 'Save');

		$form->setDefaults(array(
			'name' => $page->name,
			'content' => $page->content,
		));

		$model = $this->pages;
		$form->onSuccess[] = function(Form $form) use($model, $page, $user) {
			$values = $form->getValues();
			$model->updatePage($page->id, $user->getId(), $values->name, $values->content);
		};

		return $form;
	}

}
