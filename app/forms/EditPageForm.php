<?php

namespace NetteAddons\Forms;

use Nette\Security\IIdentity,
	Nette\Database\Table\ActiveRow,
	NetteAddons\Model\Pages;


/**
 * @author  Patrik Votoček
 *
 * @property \Nette\Database\Table\ActiveRow $page
 * @property-write \Nette\Security\IIdentity $user
 */
class EditPageForm extends BaseForm
{

	/** @var \NetteAddons\Model\Pages */
	private $pages;

	/** @var \Nette\Database\Table\ActiveRow */
	private $page;

	/** @var \Nette\Security\IIdentity */
	private $user;


	/**
	 * @param \NetteAddons\Model\Pages
	 */
	public function __construct(Pages $pages)
	{
		$this->pages = $pages;
		parent::__construct();
	}


	/**
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @param \Nette\Database\Table\ActiveRow;
	 * @return EditPageForm
	 */
	public function setPage(ActiveRow $page)
	{
		$this->page = $page;

		$this->setDefaults(array(
			'name' => $page->name,
			'content' => $page->content,
		));

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



	protected function buildForm()
	{
		$this->addText('name', 'Name')->setRequired()->setAttribute('class', 'text input-half');
		$this->addTextArea('content', 'Content')->setRequired()->setAttribute('class', 'fullscreenable');

		$this->addSubmit('sub', 'Save');

		$this->onSuccess[] = $this->process;
	}



	public function process()
	{
		$values = $this->getValues();

		$this->pages->updatePage($this->page->id, $this->user->getId(), $values->name, $values->content);
	}

}
