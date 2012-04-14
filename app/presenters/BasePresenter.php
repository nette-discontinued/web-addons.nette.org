<?php

namespace NetteAddons;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{

	public function createTemplate($class = NULL)
	{
		return $this->context->templateFactory->createTemplate(NULL, $this);
	}

}
