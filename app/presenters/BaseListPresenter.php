<?php

namespace NetteAddons;

use Nette\Utils\Callback;


abstract class BaseListPresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Addons
	 */
	public $addons;

	/**
	 * @inject
	 * @var \NetteAddons\Model\AddonVotes
	 */
	public $addonVotes;


	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->addonVotes = Callback::closure($this->addonVotes, 'calculatePopularity');
	}
}
