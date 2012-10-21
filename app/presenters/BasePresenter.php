<?php

namespace NetteAddons;

use NetteAddons\Model,
	NetteAddons\Model\Authorizator,
	Nette\Application\UI;



abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
	const CSRF_TOKEN_KEY = '_sec';

	/** @var Authorizator */
	protected $auth;

	/** @var HelperLoader */
	private $helperLoader;

	/** @var Model\Tags */
	protected $tags;

	/** @var Model\Utils\Licenses */
	protected $licenses;

	/** @var TextPreprocessor */
	protected $textPreprocessor;



	public function injectAuthorizator(Authorizator $auth)
	{
		$this->auth = $auth;
	}


	/**
	 * @param HelperLoader
	 */
	public function injectHelperLoader(HelperLoader $loader)
	{
		$this->helperLoader = $loader;
	}



	public function injectTags(Model\Tags $tags)
	{
		$this->tags = $tags;
	}



	public function injectLicenses(Model\Utils\Licenses $licenses)
	{
		$this->licenses = $licenses;
	}



	public function injectTextPreprocessor(TextPreprocessor $factory)
	{
		$this->textPreprocessor = $factory;
	}


	/**
	 * @param string|NULL
	 * @return \Nette\Templating\ITemplate
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate();
		$template->registerHelperLoader($this->helperLoader);
		return $template;
	}



	/**
	 * Zpracuje anotace u dané metody
	 *
	 * Podporované anotace:
	 *    - @secured
	 *
	 * @author   Jan Tvrdík
	 * @param    string            název metody
	 * @return   void
	 */
	private function processMethodAnnotations($method)
	{
		if (!method_exists($this, $method)) return; // přeskočí zpracování, pokud metoda neexistuje

		$reflection = $this->getReflection()->getMethod($method);
		$annotations = $reflection->getAnnotations();

		if (isset($annotations['secured'])) {
			$protectedParams = array();
			foreach ($reflection->getParameters() as $param) {
				if ($param->isOptional()) continue;
				$protectedParams[$param->name] = $this->getParameter($param->name);
			}
			if ($this->getParameter('__secu') !== $this->createSecureHash($protectedParams)) {
				throw new ForbiddenRequestException('Secured parameters are not valid.');
			}
		}
	}


// === Zabezpečení signalů =====================================================

	/**
	 * Zajistí vyhodnocení anotací nad handlerem signálu.
	 *
	 * @author   Jan Skrasek, Jan Tvrdík
	 * @param    string            název signálu
	 * @return   void
	 * @throws   BadSignalException
	 */
	public function signalReceived($signal)
	{
		$this->processMethodAnnotations($this->formatSignalMethod($signal));
		parent::signalReceived($signal);
	}

	/**
	 * Generates link. If links points to @secure annotated signal handler method, additonal
	 * parameter preventing changing parameters will be added.
	 *
	 * @author Jan Skrasek
	 * @param string  $destination
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
		$lastRequest = $this->presenter->lastCreatedRequest;

		// spatny link
		if ($lastRequest === NULL) return $link;

		// neni signal
		if (substr($destination, - 1) !== '!') return $link;

		// jen na stejny presenter
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

		// jen komponenty
		if (!$component instanceof \Nette\Application\UI\PresenterComponent) return $link;

		$method = $component->formatSignalMethod($signal);
		$reflection = \Nette\Reflection\Method::from($component, $method);

		// nema anotaci
		if (!$reflection->hasAnnotation('secured')) return $link;

		$origParams = $lastRequest->getParameters();
		$protectedParams = array();
		foreach ($reflection->getParameters() as $key => $param) {
			if ($param->isOptional()) continue;
			$protectedParams[$param->name] = $origParams[$component->getParameterId($param->name)];
		}

		$uniqueId = $this->getUniqueId();
		if (empty($uniqueId)) {
			$paramName = $component->getParameterId('__secu');
		} else {
			$paramName = substr($component->getParameterId('__secu'), strlen($uniqueId) + 1);
		}

		$args[$paramName] = $this->createSecureHash($protectedParams);

		return parent::link($destination, $args);
	}


	/**
	 * Creates secure hash from array of arguments.
	 *
	 * @author Jan Skrasek
	 * @param array $param
	 * @return string
	 */
	protected function createSecureHash($params)
	{
		$ns = $this->getSession('securedlinks');
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
		$this->template->robots = 'noindex, nofollow'; // TODO: remove in final version
	}
}
