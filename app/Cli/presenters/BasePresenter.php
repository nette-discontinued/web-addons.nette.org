<?php

namespace NetteAddons\Cli;

use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
	/**
	 * @persistent
	 * @var boolean
	 */
	public $verbose = FALSE;

	/**
	 * @param string
	 */
	protected function write($output)
	{
		echo $output;
	}

	/**
	 * @param string
	 */
	protected function writeln($line = '')
	{
		if ($this->verbose) {
			echo $line . PHP_EOL;
		}
	}

	protected function startup()
	{
		parent::startup();

		if ($this->getParameter('debug', FALSE)) {
			\Nette\Diagnostics\Debugger::$productionMode = FALSE;
		}
	}

	protected function beforeRender()
	{
		parent::beforeRender();

		$this->terminate();
	}
}
