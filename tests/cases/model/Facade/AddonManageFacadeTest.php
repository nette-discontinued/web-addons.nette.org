<?php

namespace NetteAddons\Test;

use Mockery,
	NetteAddons,
	NetteAddons\Model\Addon,
	NetteAddons\Model\Facade\AddonManageFacade;



/**
 * @author Jan Tvrdík
 */
class AddonManageFacadeTest extends TestCase
{
	/** @var AddonManageFacade */
	private $facade;



	protected function setUp()
	{
		parent::setUp();
		$this->facade = new AddonManageFacade('...', '...');
	}



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


		$addon2 = $this->facade->import($importer, $identity);
		$this->assertSame($addon, $addon2);
		$this->assertSame(123, $addon->userId);
	}



	/**
	 * @dataProvider repositoryUrlProvider
	 */
	public function testTryNormalizeRepositoryUrl($input, $expectedUrl, $expectedHosting)
	{
		$url = $this->facade->tryNormalizeRepoUrl($input, $hosting);
		$this->assertSame($expectedUrl, $url);
		$this->assertSame($expectedHosting, $hosting);
	}



	/**
	 * @author Patrik Votoček
	 * @author Jan Tvrdík
	 */
	public static function repositoryUrlProvider()
	{
		return array(
			array('https://github.com/smith/browser', 'https://github.com/smith/browser', 'github'),
			array('http://github.com/smith/browser', 'https://github.com/smith/browser', 'github'),
			array('github.com/smith/browser', 'https://github.com/smith/browser', 'github'),
			array('https://github.com/smith/browser/commits/master', 'https://github.com/smith/browser', 'github'),
			array('https://github.com/smith/browser.git', 'https://github.com/smith/browser', 'github'),
			array('git://github.com/smith/browser.git', 'https://github.com/smith/browser', 'github'),
			array('https://bitbucket.org/jiriknesl/mockista', 'https://bitbucket.org/jiriknesl/mockista', /*'bitbucket'*/NULL),
			array('http://example.com/foo', 'http://example.com/foo', NULL),
			array('example.com/foo', 'http://example.com/foo', NULL),
		);
	}
}
