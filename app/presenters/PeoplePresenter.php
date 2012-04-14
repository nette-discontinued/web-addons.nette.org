<?php

namespace NetteAddons;

/**
 * @author Jan Marek
 */
class PeoplePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->users = $this->context->users->findAuthors();
	}

}
