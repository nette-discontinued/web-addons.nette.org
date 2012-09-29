<?php

namespace NetteAddons;

use Kdyby;
use NetteAddons\Model\Addon;
use NetteAddons\Model\Authorizator;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class WikimenuControl extends Nette\Application\UI\Control
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
	 * @param Model\Addon $addon
	 */
	public function setAddon(Addon $addon)
	{
		$this->addon = $addon;
	}



	public function render()
	{
		$this->template->auth = $this->auth;
		$this->template->addon = $this->addon;

		$this->template->setFile(__DIR__ . '/Wikimenu.latte');
		$this->template->render();
	}

}
