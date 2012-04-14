<?php

namespace NetteAddons;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{

	public function createTemplate($class = NULL)
	{
		return $this->context->templateFactory->createTemplate(NULL, $this);
	}

	protected function beforeRender()
	{
		$this->template->categories = $this->context->tags->findMainTags();
		$this->template->tags = $this->context->tags; // pro praci s tagama
	}

}
