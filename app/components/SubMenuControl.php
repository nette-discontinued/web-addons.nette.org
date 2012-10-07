<?php

namespace NetteAddons;

use Nette;
use NetteAddons\Model\Addon;
use NetteAddons\Model\Authorizator;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SubMenuControl extends Nette\Application\UI\Control
{

	/** @var Authorizator */
	protected $auth;

	/** @var Addon */
	private $addon;



	/**
	 * @param Model\Authorizator $auth
	 */
	public function __construct(Authorizator $auth)
	{
		parent::__construct();
		$this->auth = $auth;
	}



	/**
	 * @param Model\Addon
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
