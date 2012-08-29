<?php

namespace NetteAddons\Test;

use NetteAddons;
use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonVersion;
use NetteAddons\Model\Utils\Composer;
use stdClass;



/**
 * @author Jan Tvrdík
 */
class ComposerTest extends TestCase
{
	public function testConstruct()
	{
		$this->setExpectedException('NetteAddons\StaticClassException');
		$obj = new Composer();
	}



	/**
	 * @todo Improve this test. Try createComposerJson without second parameter.
	 */
	public function testCreateComposerJson()
	{
		$addons = array();
		$addon = new Addon();
		$addon->name = 'Smith\'s Browser';
		$addon->composerName = 'smith/browser';
		$addon->userId = 8;
		$addon->shortDescription = 'Next-gen browser by legendary John Smith';
		$addon->description = 'desc';
		$addon->defaultLicense = 'MIT';
		$addon->repository = 'https://github.com/smith/browser';

		$addon->versions[] = $version = new AddonVersion();
		$version->addon = $addon;
		$version->version = '1.3.7';
		$version->license = 'GPL';
		$version->distType = 'zip';
		$version->distUrl = 'http://smith.com/browser.zip';

		$comp = Composer::createComposerJson(
			$version,
			(object) array(
				'name' => 'smith/browser',
				'description' => 'desc2',
				'authors' => array(
					(object) array(
						'name' => 'John Smith',
					),
				)
			)
		);

		$this->assertSame('smith/browser', $comp->name);
		$this->assertSame('desc2', $comp->description);
		$this->assertSame(array('GPL'), $comp->license);
	}



	public function testCreatePackagesJson()
	{
		$compA = new stdClass();
		$compB = new stdClass();

		$addons = array();
		$addons[] = $addon = new Addon();
		$addon->composerName = 'smith/browser';

		$addon->versions[] = $version = new AddonVersion();
		$version->version = '1.3.7';
		$version->composerJson = $compA;

		$addon->versions[] = $version = new AddonVersion();
		$version->version = '1.5.0';
		$version->composerJson = $compB;

		$file = Composer::createPackagesJson($addons);
		$this->assertInstanceOf('stdClass', $file);
		$this->assertInstanceOf('stdClass', $file->packages);
		$this->assertSame($compA, $file->packages->{'smith/browser'}->{'1.3.7'});
		$this->assertSame($compB, $file->packages->{'smith/browser'}->{'1.5.0'});
	}
}
