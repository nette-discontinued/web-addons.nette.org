<?php

namespace NetteAddons;

use Nette;



/**
 * Templates factory. Used to create templates in both presenters and components.
 *
 * @author Jan Tvrdík
 * @author Petr Procházka
 */
class TemplateFactory extends Nette\Object
{
	/** @var Nette\Caching\IStorage */
	private $cacheStorage;

	/** @var Nette\Localization\ITranslator|NULL */
	private $translator;



	/**
	 * @param Nette\Caching\IStorage cache storage for templates
	 * @param Nette\Localization\ITranslator|NULL
	 */
	public function __construct(Nette\Caching\IStorage $cacheStorage, Nette\Localization\ITranslator $translator = NULL)
	{
		$this->cacheStorage = $cacheStorage;
		$this->translator = $translator;
	}



	/**
	 * Creates and configures template.
	 *
	 * Mostly based on {@link Nette\Application\UI\Control::createTemplate()}.
	 *
	 * @param  string                       path to template
	 * @param  Nette\Application\UI\Control control which will be available in $tpl->control
	 * @return Nette\Templating\ITemplate
	 * @throws Nette\FileNotFoundException if template does not exist
	 */
	public function createTemplate($file = NULL, Nette\Application\UI\Control $control = NULL)
	{
		$template = new Nette\Templating\FileTemplate($file);
		$template->setCacheStorage($this->cacheStorage);

		// Filters
		$template->onPrepareFilters[] = function ($template) {
			$template->registerFilter(new Nette\Latte\Engine);
		};

		// Helpers
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		$template->registerHelper('texy', new TexyHelper());

		if ($this->translator)
		{
			$template->setTranslator($this->translator);
		}

		if ($control) {
			$presenter = $control->getPresenter(FALSE);
			$template->_control = $template->control = $control;
			$template->_presenter = $template->presenter = $presenter;

			if ($presenter) {
				$template->user = $presenter->getUser();
				$template->netteHttpResponse = $presenter->getService('httpResponse');
				$template->netteCacheStorage = $presenter->getService('cacheStorage');
				$template->baseUri = $template->baseUrl = rtrim($presenter->getService('httpRequest')->getUrl()->getBaseUrl(), '/');
				$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);

				if ($presenter->hasFlashSession())
				{
					$id = $control->getParameterId('flash');
					$template->flashes = $presenter->getFlashSession()->$id;
				}
			}
		}

		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

		return $template;
	}
}
