<?php

/**
 * Test: NetteAddons\Model\Importers\AddonVersionImporters\PackagistImporter
 *
 * @testcase
 */

namespace NetteAddons\Test\Model;

use Tester\Assert;
use NetteAddons\Test\TestCase;
use NetteAddons\Model\Importers\AddonVersionImporters\PackagistImporter;
use NetteAddons\Model\AddonEntity;
use NetteAddons\Model\AddonVersionEntity;
use NetteAddons\Model\AddonDependencyEntity;

require_once __DIR__ . '/../../../../../bootstrap.php';

class PackagistImporterTest extends TestCase
{
	/** @var \NetteAddons\Model\Importers\AddonVersionImporters\PackagistImporter */
	private $importer;

	public function setUp()
	{
		parent::setUp();

		$this->importer = new PackagistImporter;
	}

	public function testSupported()
	{
		Assert::true($this->importer->isSupported('packagist.org/packages/nette/nette'));
		Assert::true($this->importer->isSupported('http://packagist.org/packages/nette/nette'));
		Assert::true($this->importer->isSupported('https://packagist.org/packages/nette/nette'));
		Assert::true($this->importer->isSupported('www.packagist.org/packages/nette/nette'));
		Assert::true($this->importer->isSupported('http://www.packagist.org/packages/nette/nette'));
		Assert::true($this->importer->isSupported('https://www.packagist.org/packages/nette/nette'));
	}

	public function testNotSupported()
	{
		Assert::false($this->importer->isSupported('packagist.com/packages/nette/nette'));
		Assert::false($this->importer->isSupported('packagist.org/package/nette/nette'));
		Assert::false($this->importer->isSupported('packagist.org/nette/nette'));
		Assert::false($this->importer->isSupported('github.com/nette/nette'));
	}

	/**
	 * @throws \NetteAddons\Model\Importers\AddonVersionImporters\AddonNotFoundException
	 */
	public function testGetInvalidAddon()
	{
		$this->importer->getAddon('https://packagist.org/packages/nettte/nettte');
	}

	public function testGetAddon()
	{
		$expected = $this->getNetteAddon();
		$actual = $this->importer->getAddon('packagist.org/packages/nette/nette');

		$this->compareAddons($expected, $actual);
	}

	private function compareAddons(AddonEntity $expected, AddonEntity $actual)
	{
		Assert::equal($expected->getComposerFullName(), $actual->getComposerFullName());
		Assert::equal($expected->getComposerVendor(), $actual->getComposerVendor());
		Assert::equal($expected->getComposerName(), $actual->getComposerName());
		Assert::equal($expected->getPerex(), $actual->getPerex());
		Assert::equal($expected->getGithub(), $actual->getGithub());
		Assert::equal($expected->getPackagist(), $actual->getPackagist());

		foreach ($expected->getVersions() as $v => $expectedVersion) {
			if (!isset($actual->getVersions()[$v])) {
				Assert::fail('Missing version ' . $v);
			}

			$actualVersion = $actual->getVersions()[$v];

			$this->compareAddonVersion($expectedVersion, $actualVersion);
		}
	}

	private function compareAddonVersion(AddonVersionEntity $expected, AddonVersionEntity $actual)
	{
		Assert::equal($expected->getComposerFullName(), $actual->getComposerFullName());
		Assert::equal($expected->getVersion(), $actual->getVersion());
		Assert::equal($expected->getSuggest(), $actual->getSuggest());

		foreach ($expected->getDependencies() as $d => $expectedDependency) {
			if (!isset($actual->getDependencies()[$d])) {
				Assert::fail('Missing dependency "' . $d . '" in version ' . $actual->getVersion());
			}

			$actualDependency = $actual->getDependencies()[$d];

			$this->compareAddonDependency($expectedDependency, $actualDependency);
		}
	}

	private function compareAddonDependency(AddonDependencyEntity $expected, AddonDependencyEntity $actual)
	{
		Assert::equal($expected->getComposerFullName(), $actual->getComposerFullName());
		Assert::equal($expected->getVersion(), $actual->getVersion());
		Assert::equal($expected->getType(), $actual->getType());
		Assert::equal($expected->getDependencyName(), $actual->getDependencyName());
		Assert::equal($expected->getDependencyVersion(), $actual->getDependencyVersion());
	}

	/**
	 * @return \NetteAddons\Model\AddonEntity
	 */
	private function getNetteAddon()
	{
		$addon = new AddonEntity('nette/nette');
		$addon->setPerex('Nette Framework - innovative framework for fast and easy development of secured web applications in PHP. Write less, have cleaner code and your work will bring you joy.');
		$addon->setGithub('https://github.com/nette/nette');
		$addon->setPackagist('https://packagist.org/packages/nette/nette');

		$version = new AddonVersionEntity('nette/nette', 'v2.1.2');
		$version->addLicense('BSD-3-Clause');
		$version->addLicense('GPL-2.0');
		$version->addLicense('GPL-3.0');

		$dependency = new AddonDependencyEntity(
			'nette/nette',
			'v2.1.2',
			AddonDependencyEntity::TYPE_REQUIRE,
			'php',
			'>=5.3.1'
		);

		$version->addDependency($dependency);

		$dependency = new AddonDependencyEntity(
			'nette/nette',
			'v2.1.2',
			AddonDependencyEntity::TYPE_REQUIRE,
			'ext-iconv',
			'*'
		);

		$version->addDependency($dependency);

		$dependency = new AddonDependencyEntity(
			'nette/nette',
			'v2.1.2',
			AddonDependencyEntity::TYPE_REQUIRE,
			'ext-tokenizer',
			'*'
		);

		$version->addDependency($dependency);

		$dependency = new AddonDependencyEntity(
			'nette/nette',
			'v2.1.2',
			AddonDependencyEntity::TYPE_REQUIRE_DEV,
			'nette/tester',
			'~1.0.0'
		);

		$version->addDependency($dependency);

		$addon->addVersion($version);

		return $addon;
	}
}

id(new PackagistImporterTest)->run();
