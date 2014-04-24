<?php

namespace NetteAddons\Cli\Cron;

use DateTime;
use Nette\Application\Application;
use Nette\Database\Context;
use Nette\Utils\Callback;

class BasePresenter extends \NetteAddons\Cli\BasePresenter
{
	const CRON_TABLE = 'cron';

	/** @var \Nette\Database\Context */
	private $db;

	/** @var \Nette\Application\Application */
	private $application;

	public function injectCronBase(Context $db, Application $application)
	{
		$this->db = $db;
		$this->application = $application;
	}

	protected function startup()
	{
		$name = $this->getAction(TRUE);

		if ($this->db->table(self::CRON_TABLE)->where('name = ? AND stop IS NULL', $name)->count() > 0) {
			$this->terminate();
		}

		$startTime = new DateTime();
		$startTime->setTimestamp($_SERVER['REQUEST_TIME']);
		$row = $this->db->table(self::CRON_TABLE)->insert(array(
			'server' => php_uname('n'),
			'name' => $name,
			'start' => $startTime,
		));

		$table = $this->db->table(self::CRON_TABLE);
		$callback = function($result) use($table, $row) {
			/** @var \Nette\Database\Table\ActiveRow $row */
			$row->update(array(
				'stop' => new DateTime,
				'result' => $result,
				'time' => round((microtime(TRUE) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000),
				'memory' => memory_get_peak_usage(TRUE) / 1024,
			));
		};

		$this->onShutdown[] = function() use($callback) {
			Callback::invoke($callback, 'done');
		};

		$this->application->onError[] = function() use($callback) {
			Callback::invoke($callback, 'error');
		};

		parent::startup();
	}
}
