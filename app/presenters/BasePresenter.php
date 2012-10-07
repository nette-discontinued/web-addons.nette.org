<?php

namespace NetteAddons;

use NetteAddons\Model;
use NetteAddons\Model\Authorizator;
use Nette\Application\UI;



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



	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		// secured signals
		if (substr($destination, -1) === '!' && strpos($signal = rtrim($destination, '!'), self::NAME_SEPARATOR) === FALSE) {
			$reflection = $this->getReflection();
			$method = $this->formatSignalMethod($signal);
			$signalReflection = $reflection->getMethod($method);

			if ($signalReflection->hasAnnotation('secured')) {
				$signalParams = array();
				foreach ($signalReflection->getParameters() as $param) {
					if (isset($args[$param->name])) {
						$signalParams[$param->name] = $args[$param->name];
					}
				}
				$args[self::CSRF_TOKEN_KEY] = $this->getCsrfToken($method, $signalParams);
			}
		}

		try {
			return $this->getPresenter()->createRequest($this, $destination, $args, 'link');
		} catch (UI\InvalidLinkException $e) {
			return $this->getPresenter()->handleInvalidLink($e);
		}
	}



	public function signalReceived($signal)
	{
		$method = $this->formatSignalMethod($signal);

		if (method_exists($this, $method)) {
			$reflection = new \Nette\Reflection\Method($this, $method);
			if ($reflection->hasAnnotation('secured')) {
				$params = array();
				if ($this->params) {
					foreach ($reflection->getParameters() as $param) {
						if (isset($this->params[$param->name])) {
							$params[$param->name] = $this->params[$param->name];
						}
					}
				}
				if (!isset($this->params[self::CSRF_TOKEN_KEY]) || $this->params[self::CSRF_TOKEN_KEY] !== $this->getCsrfToken($method, $params)) {
					throw new UI\BadSignalException("Invalid security token for signal '$signal' in class {$this->reflection->name}.");
				}
			}
		}

		parent::signalReceived($signal);

		if (!$this->isAjax() && isset($this->params[self::CSRF_TOKEN_KEY])) {
			throw new \RuntimeException("Secured signal '$signal' did not redirect. Possible csrf-token reveal by http referer header.");
		}
	}



	protected function getCsrfToken($method, $params)
	{
		$control = get_class($this);
		$session = $this->getSession('Addons.Presenter/CSRF');
		if (!isset($session->token)) {
			$session->token = \Nette\Utils\Strings::random();
		}

		$params = \Nette\Utils\Arrays::flatten($params);
		$params = implode('|', array_keys($params)) . '|' . implode('|', array_values($params));
		return substr(md5($control . $method . $params . $session->token), 0, 8);
	}



	/**
	 * @return SubMenuControl
	 */
	protected function createComponentSubMenu()
	{
		return new SubMenuControl($this->auth);
	}



	/**
	 * @return CategoriesControl
	 */
	protected function createComponentCategories()
	{
		return new CategoriesControl($this->tags);
	}



	protected function beforeRender()
	{
		$this->template->auth = $this->auth;
		$this->template->categories = $this->tags->findMainTags();
		$this->template->tags = $this->tags;
		$this->template->robots = 'noindex, nofollow'; // TODO: remove in final version
	}
}
