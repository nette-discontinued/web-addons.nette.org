<?php

namespace NetteAddons\Components;

use NetteAddons\Model\Addon;
use NetteAddons\Model\Authorizator;


class SubMenuControl extends \Nette\Application\UI\Control
{

	/** @var \NetteAddons\Model\Authorizator */
	protected $auth;

	/** @var \NetteAddons\Model\Addon|NULL */
	private $addon;

	/** @var \Nette\Database\Table\ActiveRow|string|NULL */
	private $page;


	public function __construct(Authorizator $auth)
	{
		parent::__construct();

		$this->auth = $auth;
	}


	/**
	 * @param \NetteAddons\Model\Addon
	 * @return SubMenuControl
	 */
	public function setAddon(Addon $addon)
	{
		$this->addon = $addon;
		return $this;
	}


	/**
	 * @param \Nette\Database\Table\ActiveRow|string
	 * @return SubMenuControl
	 */
	public function setPage($page)
	{
		$this->page = $page;
		return $this;
	}


	public function render()
	{
		$this->template->auth = $this->auth;
		if ($this->addon) {
			$this->template->addon = $this->addon;
		}
		if ($this->page) {
			$this->template->page = $this->page;
		}

		$this->template->setFile(__DIR__ . '/SubMenu.latte');
		$this->template->render();
	}
}
