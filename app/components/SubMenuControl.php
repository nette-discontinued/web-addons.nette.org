<?php

namespace NetteAddons\Components;

use Nette,
	NetteAddons\Model\Addon,
	NetteAddons\Model\Authorizator;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SubMenuControl extends Nette\Application\UI\Control
{

	/** @var \NetteAddons\Model\Authorizator */
	protected $auth;

	/** @var \NetteAddons\Model\Addon */
	private $addon;



	/**
	 * @param \NetteAddons\Model\Authorizator $auth
	 */
	public function __construct(Authorizator $auth)
	{
		parent::__construct();
		$this->auth = $auth;
	}



	/**
	 * @param \NetteAddons\Model\Addon
	 */
	public function setAddon(Addon $addon)
	{
		$this->addon = $addon;
		return $this;
	}



	public function render()
	{
		$this->template->auth = $this->auth;
		if ($this->addon) {
			$this->template->addon = $this->addon;
		}

		$this->template->setFile(__DIR__ . '/SubMenu.latte');
		$this->template->render();
	}

}
