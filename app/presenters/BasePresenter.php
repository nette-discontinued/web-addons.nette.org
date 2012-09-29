<?php

namespace NetteAddons;

use NetteAddons\TemplateFactory;
use NetteAddons\Model;
use NetteAddons\Model\Authorizator;
use Nette\Application\UI;



abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
	const CSRF_TOKEN_KEY = '_sec';

	/** @var Authorizator */
	protected $auth;

	/** @var TemplateFactory */
	protected $tplFactory;

	/** @var Model\Tags */
	protected $tags;



	public function injectAuthorizator(Authorizator $auth)
	{
		$this->auth = $auth;
	}



	public function injectTemplateFactory(TemplateFactory $factory)
	{
		$this->tplFactory = $factory;
	}



	public function injectTags(Model\Tags $tags)
	{
		$this->tags = $tags;
	}



	public function createTemplate($class = NULL)
	{
		return $this->tplFactory->createTemplate(NULL, $this);
	}


	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		// secured signals
		if (substr($destination, -1) === '!' && strpos($signal = rtrim($destination, '!'), self::NAME_SEPARATOR) === FALSE) {
			$reflection = new UI\PresenterComponentReflection($this);
			$method = $this->formatSignalMethod($signal);
			$signalReflection = $reflection->getMethod($method);

			if ($signalReflection->hasAnnotation('secured')) {
				$signalParams = array();
				if ($args) {
					foreach ($signalReflection->getParameters() as $param) {
						if (isset($args[$param->name])) {
							$signalParams[$param->name] = $args[$param->name];
						}
					}
					$args[self::CSRF_TOKEN_KEY] = $this->getCsrfToken($method, $signalParams);
				}
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
			\Nette\Diagnostics\Debugger::barDump($params, 'params in handle');
			if (!isset($this->params[self::CSRF_TOKEN_KEY]) || $this->params[self::CSRF_TOKEN_KEY] !== $this->getCsrfToken($method, $params)) {
				throw new UI\BadSignalException("Invalid security token for signal '$signal' in class {$this->reflection->name}.");
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



	protected function beforeRender()
	{
		$this->template->auth = $this->auth;
		$this->template->categories = $this->tags->findMainTags();
		$this->template->tags = $this->tags;
	}
}
