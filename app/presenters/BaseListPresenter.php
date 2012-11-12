<?php

namespace NetteAddons;

use NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVotes;



/**
 * @author Patrik Votoček
 */
abstract class BaseListPresenter extends BasePresenter
{
	/** @var Model\Addons */
	protected $addons;

	/** @var Model\AddonVotes */
	protected $addonVotes;


	/**
	 * @param Model\Addons
	 */
	public function injectAddons(Addons $addons)
	{
		$this->addons = $addons;
	}



	public function injectAddonsVotes(AddonVotes $addonVotes)
	{
		$this->addonVotes = $addonVotes;
	}



	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->addonVotes = callback($this->addonVotes, 'calculatePopularity');
	}

}
