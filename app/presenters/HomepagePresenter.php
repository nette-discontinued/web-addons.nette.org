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

}
