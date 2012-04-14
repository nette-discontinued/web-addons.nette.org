<?php

namespace NetteAddons;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer,
	Nette\Application\UI\Presenter;


/**
 * Base class for all forms.
 *
 * Handles the form rendering using form templates.
 */
abstract class BaseForm extends Form
{
	/** @var TemplateFactory */
	private $templateFactory;

	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->buildForm();
	}


	/**
	 * Abstract function which handles the form creation.
	 * @abstract
	 * @return void
	 */
	protected abstract function buildForm();


	public function attached($presenter)
	{
		if ($presenter instanceof Presenter) {
			$this->templateFactory = $presenter->getContext()->templateFactory;
		}
	}


	public function render()
	{
		$args = func_get_args();
		if (!$args && ($file = $this->getTemplateFile())) {
			$template = $this->templateFactory->createTemplate($file, $this->getParent());
			$template->bootstrap = $this->getLayoutTemplateFile();
			$template->form = $this;
			$template->render();
		} else {
			call_user_func_array('parent::render', $args);
		}
	}


	protected function getTemplateFile()
	{
		$refl = $this->getReflection();
		$file = dirname($refl->getFileName()) . '/' . lcFirst($refl->getShortName()) . '.latte';
		return file_exists($file) ? $file : NULL;
	}


	protected function getLayoutTemplateFile()
	{
		$baseDir = dirname(dirname($this->getReflection()->getFileName()));
		return "$baseDir/templates/@form.latte";
	}
}
