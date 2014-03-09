<?php

/**
 * Test: NetteAddons\Model\Utils\Composer
 *
 * @testcase
 */

namespace NetteAddons\Test;

use Tester\Assert;
use stdClass;
use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonVersion;
use NetteAddons\Model\Utils\Composer;

require_once __DIR__ . '/../../../bootstrap.php';

class ComposerTest extends TestCase
{
	/**
	 * @throws \NetteAddons\StaticClassException
	 */
	public function testConstruct()
	{
		$obj = new Composer();
	}



	public function testIsValid()
	{
		$valid = (object) array(
			'name' => 'smith/browser',
			'description' => 'Smith\'s Browser',
		);

		Assert::true(Composer::isValid($valid));

		Assert::false(Composer::isValid('foo'));
		Assert::false(Composer::isValid(new stdClass));
		Assert::false(Composer::isValid((object) array('name' => '...')));

		$invalid = clone $valid;
		$invalid->foo = 'bar';
		Assert::false(Composer::isValid($invalid));
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

		Assert::same('smith/browser', $comp->name);
		Assert::same('desc2', $comp->description);
		Assert::same(array('GPL'), $comp->license);

		unset($comp->dist, $comp->source); // dist and source are not part of composer.json schema
		Assert::true(Composer::isValid($comp));
	}



	public function testCreatePackagesJson()
	{
		$compA = new stdClass();
		$compB = new stdClass();

		$addons = array();
		$addons[] = $addon = new Addon();
		$compA->name = $compB->name = $addon->composerFullName = 'smith/browser';

		$addon->versions[] = $version = new AddonVersion();
		$version->version = '1.3.7';
		$version->composerJson = $compA;

		$addon->versions[] = $version = new AddonVersion();
		$version->version = '1.5.0';
		$version->composerJson = $compB;

		$file = Composer::createPackagesJson($addons);
		Assert::type('stdClass', $file);
		Assert::type('stdClass', $file->packages);
		Assert::same($compA, $file->packages->{'smith/browser'}->{'1.3.7'});
		Assert::same($compB, $file->packages->{'smith/browser'}->{'1.5.0'});
	}
}

id(new ComposerTest)->run();
