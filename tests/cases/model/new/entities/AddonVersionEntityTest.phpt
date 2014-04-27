<?php

/**
 * Test: NetteAddons\Model\AddonVersionEntity
 *
 * @testcase
 */

namespace NetteAddons\Test\Model;

use Tester\Assert;
use NetteAddons\Test\TestCase;
use NetteAddons\Model\AddonVersionEntity;

require_once __DIR__ . '/../../../../bootstrap.php';

class AddonVersionEntityTest extends TestCase
{
	public function dataInvalidComposerFullName()
	{
		return array(
			array('Nette/nette'),
			array('Nette/Nette'),
			array('nette foundation/nette framework'),
			array('nette/nette_framework'),
			array('nette/nette.framework'),
		);
	}

	public function dataInvalidString()
	{
		return array(
			array(null),
			array(42),
			array(false),
			array(array()),
			array(new \stdClass),
		);
	}

	/**
	 * @dataProvider dataInvalidComposerFullName
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidComposerFullName($composerName)
	{
		new AddonVersionEntity($composerName, '1.0.0');
	}

	/**
	 * @dataProvider dataInvalidString
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidVersion($version)
	{
		new AddonVersionEntity('nette/addons-portal', $version);
	}

	/**
	 * @dataProvider dataInvalidString
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidLicense($license)
	{
		$version = new AddonVersionEntity('nette/addons-portal', '1.0.0');
		$version->addLicense($license);
	}

	public function testLicenses()
	{
		$addon = new AddonVersionEntity('nette/addons-portal', '1.0.0');
		Assert::equal(0, count($addon->getLicenses()));
		Assert::equal(0, count($addon->licenses));
		Assert::type('array', $addon->getLicenses());
		Assert::equal(array(), $addon->getLicenses());
	}

	public function testComposerFullName()
	{
		$addon = new AddonVersionEntity('nette/addons-portal', '1.0.0');
		Assert::equal('nette/addons-portal', $addon->getComposerFullName());
		Assert::equal('nette/addons-portal', $addon->composerFullName);
	}

	public function testVersion()
	{
		$addon = new AddonVersionEntity('nette/addons-portal', '1.0.0');
		Assert::equal('1.0.0', $addon->getVersion());
		Assert::equal('1.0.0', $addon->version);
	}

	public function testDependency()
	{
		$addon = new AddonVersionEntity('nette/addons-portal', '1.0.0');
		Assert::equal(0, count($addon->getDependencies()));
		Assert::equal(0, count($addon->dependencies));
		Assert::type('array', $addon->getDependencies());
		Assert::equal(array(), $addon->getDependencies());
	}

	/**
	 * @dataProvider dataInvalidComposerFullName
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testAddSuggestInvalidComposerName($composerName)
	{
		$addon = new AddonVersionEntity('nette/addons-portal', '1.0.0');
		$addon->addSuggest($composerName, 'test');
	}

	/**
	 * @dataProvider dataInvalidString
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testAddSuggestInvalidDescription($description)
	{
		$addon = new AddonVersionEntity('nette/addons-portal', '1.0.0');
		$addon->addSuggest('nette/nette', $description);
	}
}

id(new AddonVersionEntityTest)->run();
