<?php

namespace NetteAddons\Cli\Cron;

use Nette\Database\Context;
use NetteAddons\Model\Addon;
use NetteAddons\Services\AddonUpdaterService;

class AddonUpdaterPresenter extends BasePresenter
{
	/** @var \NetteAddons\Services\AddonUpdaterService */
	private $addonUpdaterService;
	/** @var \Nette\Database\Context */
	private $db;

	public function __construct(AddonUpdaterService $addonUpdaterService, Context $db)
	{
		parent::__construct();

		$this->addonUpdaterService = $addonUpdaterService;
		$this->db = $db;
	}

	public function actionUpdate()
	{
		$query = $this->db->table('addons')->where('type = ? AND deletedAt IS NULL', Addon::TYPE_COMPOSER);
		foreach ($query as $row) {
			$this->addonUpdaterService->updateAddon($row->id);
			$this->writeln('Updating addon "' . $row->name . '".');
		}
	}
}
