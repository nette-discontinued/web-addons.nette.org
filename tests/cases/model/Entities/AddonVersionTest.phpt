<?php

/**
 * Test: NetteAddons\Model\AddonVersion
 *
 * @testcase
 */

namespace NetteAddons\Test;

use Tester\Assert;
use Mockery;
use NetteAddons\Model\AddonVersion;

require_once __DIR__ . '/../../../bootstrap.php';

class AddonVersionTest extends TestCase
{
	public function testFromActiveRow()
	{
		$row = $this->createRow(array(
			'id' => 123,
			'addonId' => 456,
			'version' => '1.3.7',
			'license' => 'BSD-3',
			'distType' => 'zip',
			'distUrl' => 'http://smith.name/browser.zip',
			'sourceType' => NULL,
			'sourceUrl' => NULL,
			'sourceReference' => NULL,
			'composerJson' => '{"a": "b"}',
			'updatedAt' => NULL,
		));

		$row->shouldReceive('related')
			->with('dependencies')
			->andReturn(array(
				$this->createRow(array(
					'id' => 87988,
					'addonId' => 456,
					'dependencyId' => NULL,
					'packageName' => 'foo/bar',
					'version' => '>2.0',
					'type' => 'require',
				)),
			));


		$version = AddonVersion::fromActiveRow($row);
		Assert::type('NetteAddons\Model\AddonVersion', $version);
		Assert::same('1.3.7', $version->version);
		Assert::same('BSD-3', $version->license);
		Assert::same(array('foo/bar' => '>2.0'), $version->require);
		// Assert::same(array(), $version->suggest);
		Assert::same(array(), $version->provide);
		Assert::same(array(), $version->replace);
		Assert::same(array(), $version->conflict);
		Assert::same(array(), $version->recommend);
		Assert::same('zip', $version->distType);
		Assert::same('http://smith.name/browser.zip', $version->distUrl);
		Assert::same(NULL, $version->sourceType);
		Assert::same(NULL, $version->sourceUrl);
		Assert::same(NULL, $version->sourceReference);
		Assert::equal((object) array('a' => 'b'), $version->composerJson);
		Assert::same(NULL, $version->addon);
	}



	private function createRow($data)
	{
		// ugly, ugly!
		$table = Mockery::mock()->shouldIgnoreMissing();
		$table->shouldReceive('getDatabaseReflection->getBelongsToReference')->andReturn(array('a', 'b'));
		$row = Mockery::mock('Nette\Database\Table\ActiveRow');

		Access('Nette\Database\Table\ActiveRow', '$data')
			->asInstance($row)
			->set($data);

		Access('Nette\Database\Table\ActiveRow', '$table')
			->asInstance($row)
			->set($table);

		return $row;
	}



	public function testGetLinkTypes()
	{
		$types = AddonVersion::getLinkTypes();
		Assert::type('array', $types);
		Assert::false(\Nette\Utils\Validators::isList($types));
	}
}

id(new AddonVersionTest)->run();
