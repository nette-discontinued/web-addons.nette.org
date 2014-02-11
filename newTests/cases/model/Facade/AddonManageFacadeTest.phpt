<?php

/**
 * Test: NetteAddons\Model\Facade\AddonManageFacade
 *
 * @testcase
 */

namespace NetteAddons\Test;

use Tester\Assert;
use Mockery;
use NetteAddons\Model\Addon;
use NetteAddons\Model\Facade\AddonManageFacade;

require_once __DIR__ . '/../../../bootstrap.php';

class AddonManageFacadeTest extends TestCase
{
	/** @var AddonManageFacade */
	private $facade;



	protected function setUp()
	{
		parent::setUp();
		$session = Mockery::mock('Nette\Http\Session');
		$session->shouldReceive('getSection')->andReturn(new \Nette\Http\SessionSection($session, 'foo'));
		$this->facade = new AddonManageFacade($session, '...', '...');
	}



	public function testImport()
	{
		$importer = Mockery::mock('NetteAddons\Model\Importers\GitHubImporter');
		$identity = Mockery::mock('Nette\Security\IIdentity');
		$addons = Mockery::mock('NetteAddons\Model\Addons');
		$addon = new Addon();

		$importer->shouldReceive('import')
			->once()->withNoArgs()
			->andReturn($addon);

		$identity->shouldReceive('getId')
			->once()->withNoArgs()
			->andReturn(123);


		$addon2 = $this->facade->import($importer, $identity);
		Assert::same($addon, $addon2);
		Assert::same(123, $addon->userId);
	}
}

id(new AddonManageFacadeTest)->run();