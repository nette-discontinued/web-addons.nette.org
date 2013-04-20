<?php

namespace NetteAddons;

use NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVotes;



/**
 * @author Patrik Votoček
 */
abstract class BaseListPresenter extends BasePresenter
{
	/**
	 * @var Model\Addons
	 * @inject
	 */
	public $addons;

	/**
	 * @var Model\AddonVotes
	 * @inject
	 */
	public $addonVotes;



	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->addonVotes = callback($this->addonVotes, 'calculatePopularity');
	}

}
