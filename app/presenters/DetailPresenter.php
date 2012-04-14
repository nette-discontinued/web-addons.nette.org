<?php

namespace NetteAddons;

/**
 * @author Jan Marek
 */
class DetailPresenter extends BasePresenter
{

	public function renderDefault($id)
	{
		$addon = $this->context->addons->find($id);
		if (!$addon) $this->error('Addon not found!');
		$this->template->addon = $addon;
	}

}
