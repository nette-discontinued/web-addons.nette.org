<?php

namespace NetteAddons\Cli;

class HelpPresenter extends BasePresenter
{
	public function actionDefault()
	{
		$this->writeln('Nette addons portal CLI');
		$this->writeln('-----------------------');
		$this->writeln();

		$this->writeln('Options:');
		$this->writeln('--verbose | for verbose output');
		$this->writeln();

		$this->writeln('Actions:');
		$this->writeln('Cli:Help:default | print this help');
		$this->writeln('Cli:Cron:Pages:update | update pages sources');
		$this->writeln('Cli:Cron:AddonUpdater:update | update addon & versions');
		$this->writeln();
	}

	/**
	 * @param string
	 */
	protected function writeln($line = '')
	{
		echo $line . PHP_EOL;
	}
}
