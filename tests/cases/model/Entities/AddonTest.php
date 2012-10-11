<?php

namespace NetteAddons\Test;

use NetteAddons\Model\Addon;

/**
 * @author Michael Moravec
 */
class AddonTest extends TestCase
{
	public function testGetVendorName()
	{
		$addon = $this->createAddon();
		$this->assertSame('foo', $addon->getVendorName());
	}



	public function testGetPackageName()
	{
		$addon = $this->createAddon();
		$this->assertSame('bar', $addon->getPackageName());
	}



	private function createAddon()
	{
		$addon = new Addon();
		$addon->composerName = 'foo/bar';
		return $addon;
	}
}
