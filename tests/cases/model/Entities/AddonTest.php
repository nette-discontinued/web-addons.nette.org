<?php

namespace NetteAddons\Test;

use NetteAddons\Model\Addon;

/**
 * @author Michael Moravec
 */
class AddonTest extends TestCase
{
	public function testGetComposerVendorName()
	{
		$addon = $this->createAddon();
		$this->assertSame('foo', $addon->composerVendor);
	}



	public function testGetComposerName()
	{
		$addon = $this->createAddon();
		$this->assertSame('bar', $addon->composerName);
	}



	private function createAddon()
	{
		$addon = new Addon();
		$addon->composerFullName = 'foo/bar';
		return $addon;
	}
}
