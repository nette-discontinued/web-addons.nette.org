<?php

namespace NetteAddons\Test;

use Mockery,
	NetteAddons\Model\AddonVersion;



/**
 * @author Jan Tvrdík
 */
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
			'downloadsCount' => 0,
			'installsCount' => 0,
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
		$this->assertInstanceOf('NetteAddons\Model\AddonVersion', $version);
		$this->assertSame('1.3.7', $version->version);
		$this->assertSame('BSD-3', $version->license);
		$this->assertSame(array('foo/bar' => '>2.0'), $version->require);
		// $this->assertSame(array(), $version->suggest);
		$this->assertSame(array(), $version->provide);
		$this->assertSame(array(), $version->replace);
		$this->assertSame(array(), $version->conflict);
		$this->assertSame(array(), $version->recommend);
		$this->assertSame('zip', $version->distType);
		$this->assertSame('http://smith.name/browser.zip', $version->distUrl);
		$this->assertSame(NULL, $version->sourceType);
		$this->assertSame(NULL, $version->sourceUrl);
		$this->assertSame(NULL, $version->sourceReference);
		$this->assertEquals((object) array('a' => 'b'), $version->composerJson);
		$this->assertSame(NULL, $version->addon);
		$this->assertSame(0, $version->downloadsCount);
		$this->assertSame(0, $version->installsCount);
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
		$this->assertInternalType('array', $types);
		$this->assertFalse(\Nette\Utils\Validators::isList($types));
	}
}
