<?php

namespace NetteAddons;

class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$addons = $this->context->addons;

		$this->template->updatedAddons = $addons->findAll()->order('updated_at DESC')->limit(3);
	}

	public function handleReinstall()
	{
		$connection = $this->getContext()->databaseConnection;

		//$connection->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);

		$tables = $connection->getSupplementalDriver()->getTables();
		foreach ($tables as $table) {
			$connection->exec('SET foreign_key_checks = 0');
			$connection->exec("DROP TABLE `{$table['name']}`");
		}

		\Nette\Database\Helpers::loadFromFile($connection, APP_DIR . "/model/db/schema.sql");
		\Nette\Database\Helpers::loadFromFile($connection, APP_DIR . "/model/db/data.sql");

		$this->flashMessage('Yea!');
		$this->redirect('this');
	}


	public function actionTestVersions()
	{
		$c = callback('NetteAddons\Model\Version::create');
		dump($t = array_filter(array(
			$c('1.1.1.1'),
			$c('1.1.1'),
			$c('1.*.1'),
			$c('1.2.*'),
			$c('0.1.0'),
			$c('1.2') === NULL,
			$c('1') === NULL,
			$c('>=1.9.0'),
			$c('<=1.10.0'),
			$c('=1.11.0'),
			$c('=1.11.0')->match('1.11.0'),
			$c('1.0.0-alpha'), // [0-9A-Za-z-]
			$c('1.0.0-alpha.1'),
			$c('1.0.0-0.3.7'),
			$c('1.0.0-x.7.z.92'),
			$c('1.0.0+build.1'), // [0-9A-Za-z-]
			$c('1.3.7+build.11.e0f985a'),
			$c('1.3.7+build.11.e0f985a'),
			$c('<1.0.0-alpha.1'),
			$c('<1.0.0-alpha.1')->match('1.0.0-alpha'),
			$c('>1.0.0-beta.2')->match('1.0.0-alpha.1'),
			$c('>1.0.0-beta.11')->match('1.0.0-beta.2'),
			$c('>1.0.0-rc.1')->match('1.0.0-beta.11'),
			$c('>1.0.0-rc.1+build.1')->match('1.0.0-rc.1'),
			$c('1.0.0-rc.1+build.1'),
			$c('<1.0.0')->match('1.0.0-rc.1+build.1'),
			$c('>1.0.0+0.3.7')->match('1.0.0'),
			$c('>1.3.7+build')->match('1.0.0+0.3.7'),
			$c('>1.3.7+build.2.b8f12d7')->match('1.3.7+build'),
			$c('>1.3.7+build.11.e0f985a')->match('1.3.7+build.2.b8f12d7')
		), function ($t) { return !(bool)$t; }));

		echo "<h1 id='f'>" . ($t ? 'FAILED' : 'OK!') .  "</h1>";
		$this->terminate();
	}

}
