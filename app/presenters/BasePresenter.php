<?php

namespace NetteAddons;

use Nette\Reflection\Method;
use Nette\Application\UI\PresenterComponent;
use NetteAddons\Model;


abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Authorizator
	 */
	public $auth;

	/**
	 * @inject
	 * @var \NetteAddons\HelperLoader
	 */
	public $helperLoader;

	/**
	 * @inject
	 * @var \NetteAddons\Model\Tags
	 */
	public $tags;

	/**
	 * @inject
	 * @var \NetteAddons\Components\IPagesControlFactory
	 */
	public $pagesControlFactory;

	/**
	 * @inject
	 * @var \NetteAddons\Model\Utils\Licenses
	 */
	public $licenses;

	/**
	 * @inject
	 * @var \NetteAddons\TextPreprocessor
	 */
	public $textPreprocessor;


	/**
	 * @param string|NULL
	 * @return \Nette\Templating\ITemplate
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate();
		$template->registerHelperLoader($this->helperLoader);
		if (isset($this->context->getParameters()['googleAnalyticsCode'])) {
			$template->googleAnalyticsCode = $this->context->getParameters()['googleAnalyticsCode'];
		}
		return $template;
	}


	/**
	 * Calls signal handler method and processes the @secured annotation.
	 *
	 * @param string
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
	 * @param string
	 * @param array|mixed $args
	 * @return string
	 */
	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		$link = parent::link($destination, $args);
		$lastRequest = $this->getPresenter()->getLastCreatedRequest();

		// bad link
		if ($lastRequest === NULL) {
			return $link;
		}

		// not a signal
		if (substr($destination, - 1) !== '!') {
			return $link;
		}

		// signal must lead to this presenter
		if ($this->getPresenter()->getName() !== $lastRequest->getPresenterName()) {
			return $link;
		}

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
		if (!$component instanceof PresenterComponent) {
			return $link;
		}

		$method = $component->formatSignalMethod($signal);
		$reflection = Method::from($component, $method);

		// does not have annotation
		if (!$reflection->hasAnnotation('secured')) {
			return $link;
		}

		$origParams = $lastRequest->getParameters();
		$protectedParams = array();
		foreach ($reflection->getParameters() as $param) {
			if ($param->isOptional()) {
				continue;
			}
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
	 * @param array
	 * @return string
	 */
	protected function createSecureHash($params)
	{
		$session = $this->getSession('Addons.Presenter/CSRF');
		if ($session->key === NULL) {
			$session->key = uniqid();
		}
		$data = implode('|', array_keys($params)) . '|' . implode('|', array_values($params)) . $session->key;
		return substr(md5($data), 4, 8);
	}


	/**
	 * @return \NetteAddons\Components\SubMenuControl
	 */
	protected function createComponentSubMenu()
	{
		return new Components\SubMenuControl($this->auth);
	}

	/**
	 * @return \NetteAddons\Components\CategoriesControl
	 */
	protected function createComponentCategories()
	{
		return new Components\CategoriesControl($this->tags);
	}

	/**
	 * @return \NetteAddons\Components\PagesControl
	 */
	protected function createComponentPages()
	{
		return $this->pagesControlFactory->create();
	}

	protected function beforeRender()
	{
		$this->template->auth = $this->auth;
		$this->template->categories = $this->tags->findMainTags();
		$this->template->tags = $this->tags;
	}
}
