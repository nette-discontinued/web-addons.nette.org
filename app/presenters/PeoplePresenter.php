<?php

namespace NetteAddons;

/**
 * @author Jan Marek
 */
class PeoplePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->authors = $this->context->users->findAuthors();
	}



	public function renderDetail($id)
	{
		$this->template->author = $this->context->users->find($id);
	}

}
