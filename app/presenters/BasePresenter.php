<?php

namespace NetteAddons;

use NetteAddons\Model,
	Nette\Application\UI;



abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
	/**
	 * @var Model\Authorizator
	 * @inject
	 */
	public $auth;

	/**
	 * @var HelperLoader
	 * @inject
	 */
	public $helperLoader;

	/**
	 * @var Model\Tags
	 * @inject
	 */
	public $tags;

	/**
	 * @var Model\Pages
	 * @inject
	 */
	public $pages;

	/**
	 * @var Model\Utils\Licenses
	 * @inject
	 */
	public $licenses;

	/**
	 * @var TextPreprocessor
	 * @inject
	 */
	public $textPreprocessor;


	/**
	 * @param  string|NULL
	 * @return \Nette\Templating\ITemplate
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate();
		$template->registerHelperLoader($this->helperLoader);
		return $template;
	}



	/**
	 * Calls signal handler method and processes the @secured annotation.
	 *
	 * @author Jan Skrasek, Jan Tvrdík
	 * @param  string
	 * @return void
	 * @throws \Nette\Application\BadRequestException
	 */
	public function signalReceived($signal)
	{
		$method = $this->formatSignalMethod($signal);
		if (method_exists($this, $method)) {
			$reflection = $this->getReflection()->getMethod($method);
			$annotations = $reflection->getAnnotations();

			if (isset($annotations['secured'])) {
				$protectedParams = array();
				foreach ($reflection->getParameters() as $param) {
					if ($param->isOptional()) continue;
					$protectedParams[$param->name] = $this->getParameter($param->name);
				}
				if ($this->getParameter('__sec') !== $this->createSecureHash($protectedParams)) {
					$this->error('Secured parameters are not valid.', 403);
				}
			}
		}

		parent::signalReceived($signal);
	}



	/**
	 * Generates link. If links points to @secure annotated signal handler method, additonal
	 * parameter preventing changing parameters will be added.
	 *
	 * @author Jan Skrasek
	 * @param  string
	 * @param  array|mixed $args
	 * @return string
	 */
	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		$link = parent::link($destination, $args);
		$lastRequest = $this->presenter->lastCreatedRequest;

		// bad link
		if ($lastRequest === NULL) return $link;

		// not a signal
		if (substr($destination, - 1) !== '!') return $link;

		// signal must lead to this presenter
		if ($this->getPresenter()->getName() !== $lastRequest->getPresenterName()) return $link;

		$destination = str_replace(':', '-', $destination);
		if (strpos($destination, '-') !== FALSE) {
			$pos = strrpos($destination, '-');
			$signal = substr($destination, $pos + 1, -1);
			$component = substr($destination, 0, $pos);
			$component = $this->getComponent($component);
		} else {
			$signal = substr($destination, 0, -1);
			$component = $this;
		}

		// only components
		if (!$component instanceof \Nette\Application\UI\PresenterComponent) return $link;

		$method = $component->formatSignalMethod($signal);
		$reflection = \Nette\Reflection\Method::from($component, $method);

		// does not have annotation
		if (!$reflection->hasAnnotation('secured')) return $link;

		$origParams = $lastRequest->getParameters();
		$protectedParams = array();
		foreach ($reflection->getParameters() as $key => $param) {
			if ($param->isOptional()) continue;
			$protectedParams[$param->name] = $origParams[$component->getParameterId($param->name)];
		}

		$uniqueId = $this->getUniqueId();
		if (empty($uniqueId)) {
			$paramName = $component->getParameterId('__sec');
		} else {
			$paramName = substr($component->getParameterId('__sec'), strlen($uniqueId) + 1);
		}

		$args[$paramName] = $this->createSecureHash($protectedParams);

		return parent::link($destination, $args);
	}



	/**
	 * Creates secure hash from array of arguments.
	 *
	 * @author Jan Skrasek
	 * @param  array
	 * @return string
	 */
	protected function createSecureHash($params)
	{
		$ns = $this->getSession('Addons.Presenter/CSRF');
		if ($ns->key === NULL) {
			$ns->key = uniqid();
		}
		$s = implode('|', array_keys($params)) . '|' . implode('|', array_values($params)) . $ns->key;
		return substr(md5($s), 4, 8);
	}



	/**
	 * @return Components\SubMenuControl
	 */
	protected function createComponentSubMenu()
	{
		return new Components\SubMenuControl($this->auth);
	}



	/**
	 * @return Components\CategoriesControl
	 */
	protected function createComponentCategories()
	{
		return new Components\CategoriesControl($this->tags);
	}



	protected function beforeRender()
	{
		$this->template->auth = $this->auth;
		$this->template->categories = $this->tags->findMainTags();
		$this->template->tags = $this->tags;
		$this->template->pages = $this->pages->findAll();
	}
}
