<?php

/**
 * Test: NetteAddons\Model\AddonDependencyEntity
 *
 * @testcase
 */

namespace NetteAddons\Test\Model;

use Tester\Assert;
use NetteAddons\Test\TestCase;
use NetteAddons\Model\AddonDependencyEntity;

require_once __DIR__ . '/../../../../bootstrap.php';

class AddonDependencyEntityTest extends TestCase
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
		new AddonDependencyEntity($composerName, '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'nette/nette', '1.0.0');
	}

	/**
	 * @dataProvider dataInvalidString
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidVersion($version)
	{
		new AddonDependencyEntity('nette/addons-portal', $version, AddonDependencyEntity::TYPE_REQUIRE, 'nette/nette', '1.0.0');
	}

	/**
	 * @dataProvider dataInvalidString
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidType($type)
	{
		new AddonDependencyEntity('nette/addons-portal', '1.0.0', $type, 'nette/nette', '1.0.0');
	}

	/**
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testUnsupportedType()
	{
		new AddonDependencyEntity('nette/addons-portal', '1.0.0', 'unsupported', 'nette/nette', '1.0.0');
	}

	/**
	 * @dataProvider dataInvalidComposerFullName
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidDependencyName($composerName)
	{
		new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, $composerName, '1.0.0');
	}

	/**
	 * @dataProvider dataInvalidString
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidDependencyVersion($version)
	{
		new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'nette/nette', $version);
	}

	public function testComposerFullName()
	{
		$addon = new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'nette/nette', '1.0.0');
		Assert::equal('nette/addons-portal', $addon->getComposerFullName());
		Assert::equal('nette/addons-portal', $addon->composerFullName);
	}

	public function testVersion()
	{
		$addon = new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'nette/nette', '1.0.0');
		Assert::equal('1.0.0', $addon->getVersion());
		Assert::equal('1.0.0', $addon->version);
	}

	public function testType()
	{
		$addon = new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'nette/nette', '1.0.0');
		Assert::equal(AddonDependencyEntity::TYPE_REQUIRE, $addon->getType());
		Assert::equal(AddonDependencyEntity::TYPE_REQUIRE, $addon->type);
	}

	public function testDependencyNameComposerName()
	{
		$addon = new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'nette/nette', '1.0.0');
		Assert::equal('nette/nette', $addon->getDependencyName());
		Assert::equal('nette/nette', $addon->dependencyName);
	}

	public function testDependencyNamePHP()
	{
		$addon = new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'php', '5.5.12');
		Assert::equal('php', $addon->getDependencyName());
		Assert::equal('php', $addon->dependencyName);
	}

	public function testDependencyNameHHVM()
	{
		$addon = new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'hhvm', '3.0.1');
		Assert::equal('hhvm', $addon->getDependencyName());
		Assert::equal('hhvm', $addon->dependencyName);
	}

	public function testDependencyNameExt()
	{
		$addon = new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'ext-iconv', '*');
		Assert::equal('ext-iconv', $addon->getDependencyName());
		Assert::equal('ext-iconv', $addon->dependencyName);
	}

	public function testDependencyNameLib()
	{
		$addon = new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'lib-pcre', '*');
		Assert::equal('lib-pcre', $addon->getDependencyName());
		Assert::equal('lib-pcre', $addon->dependencyName);
	}

	public function testDependencyVersion()
	{
		$addon = new AddonDependencyEntity('nette/addons-portal', '1.0.0', AddonDependencyEntity::TYPE_REQUIRE, 'nette/nette', '1.0.0');
		Assert::equal('1.0.0', $addon->getDependencyVersion());
		Assert::equal('1.0.0', $addon->dependencyVersion);
	}
}

id(new AddonDependencyEntityTest)->run();
