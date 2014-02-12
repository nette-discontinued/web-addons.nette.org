<?php

/**
 * Test: NetteAddons\Model\Addon
 *
 * @testcase
 */

namespace NetteAddons\Test;

use Tester\Assert;
use NetteAddons\Model\Addon;

require_once __DIR__ . '/../../../bootstrap.php';

class AddonTest extends TestCase
{
	/** @var Addon */
	private $addon;



	public function setUp()
	{
		parent::setUp();

		$this->addon = new Addon();
		$this->addon->composerFullName = 'foo/bar';
	}



	public function testGetComposerVendorName()
	{
		Assert::same('foo', $this->addon->composerVendor);
	}



	public function testGetComposerName()
	{
		Assert::same('bar', $this->addon->composerName);
	}
}

id(new AddonTest)->run();