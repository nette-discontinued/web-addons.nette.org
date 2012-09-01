<?php

namespace NetteAddons\Test;

use NetteAddons;
use NetteAddons\Model\Addon;
use NetteAddons\Model\Facade\AddonManageFacade;
use Mockery;



/**
 * @author Jan Tvrdík
 */
class AddonManageFacadeTest extends TestCase
{
	public function testImport()
	{
		$importer = Mockery::mock('NetteAddons\Model\IAddonImporter');
		$identity = Mockery::mock('Nette\Security\IIdentity');
		$addons = Mockery::mock('NetteAddons\Model\Addons');
		$addon = new Addon();

		$importer->shouldReceive('import')
			->once()->withNoArgs()
			->andReturn($addon);

		$identity->shouldReceive('getId')
			->once()->withNoArgs()
			->andReturn(123);


		$facade = new AddonManageFacade($addons, '...', '...');
		$addon2 = $facade->import($importer, $identity);
		$this->assertSame($addon, $addon2);
		$this->assertSame(123, $addon->userId);
	}
}

