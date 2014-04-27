<?php

/**
 * Test: NetteAddons\Model\AddonEntity
 *
 * @testcase
 */

namespace NetteAddons\Test\Model;

use Tester\Assert;
use NetteAddons\Test\TestCase;
use NetteAddons\Model\AddonEntity;

require_once __DIR__ . '/../../../../bootstrap.php';

class AddonEntityTest extends TestCase
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

	/**
	 * @dataProvider dataInvalidComposerFullName
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidComposerFullName($composerName)
	{
		new AddonEntity($composerName);
	}

	public function testComposerFullName()
	{
		$addon = new AddonEntity('nette/addons-portal');
		Assert::equal('nette/addons-portal', $addon->getComposerFullName());
		Assert::equal('nette/addons-portal', $addon->composerFullName);
	}

	public function testComposerVendor()
	{
		$addon = new AddonEntity('nette/addons-portal');
		Assert::equal('nette', $addon->getComposerVendor());
		Assert::equal('nette', $addon->composerVendor);
	}

	public function testComposerName()
	{
		$addon = new AddonEntity('nette/addons-portal');
		Assert::equal('addons-portal', $addon->getComposerName());
		Assert::equal('addons-portal', $addon->composerName);
	}

	public function testPerex()
	{
		$addon = new AddonEntity('nette/addons-portal');
		Assert::equal('', $addon->getPerex());
		Assert::equal('', $addon->perex);

		$addon->setPerex('test');
		Assert::equal('test', $addon->getPerex());
		Assert::equal('test', $addon->perex);
	}

	public function testVersions()
	{
		$addon = new AddonEntity('nette/addons-portal');
		Assert::equal(0, count($addon->getVersions()));
		Assert::equal(0, count($addon->versions));
		Assert::type('array', $addon->getVersions());
		Assert::equal(array(), $addon->getVersions());
	}

	public function testGithub()
	{
		$addon = new AddonEntity('nette/addons-portal');
		Assert::null($addon->getGithub());
		Assert::null($addon->github);
		$addon->setGithub('https://github.com/nette/web-addons.nette.org');
		Assert::equal('https://github.com/nette/web-addons.nette.org', $addon->getGithub());
		Assert::equal('https://github.com/nette/web-addons.nette.org', $addon->github);
	}

	/**
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidGithub()
	{
		$addon = new AddonEntity('nette/addons-portal');
		$addon->setGithub('nette.org');
	}

	public function testPackagist()
	{
		$addon = new AddonEntity('nette/addons-portal');
		Assert::null($addon->getPackagist());
		Assert::null($addon->packagist);
		$addon->setPackagist('https://packagist.org/packages/nette/addons-portal');
		Assert::equal('https://packagist.org/packages/nette/addons-portal', $addon->getPackagist());
		Assert::equal('https://packagist.org/packages/nette/addons-portal', $addon->packagist);
	}

	/**
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidPackagist()
	{
		$addon = new AddonEntity('nette/addons-portal');
		$addon->setPackagist('nette.org');
	}
}

id(new AddonEntityTest)->run();
