<?php

/**
 * Test: NetteAddons\HelperLoader
 *
 * @testcase
 */

namespace NetteAddons\Test;

use Tester\Assert;
use Mockery;
use NetteAddons\HelperLoader;

require_once __DIR__ . '/../../bootstrap.php';

class HelperLoaderTest extends TestCase
{
	/** @var \NetteAddons\HelperLoader */
	private $loader;



	protected function setUp()
	{
		parent::setUp();
		$preprocessor = Mockery::mock('NetteAddons\TextPreprocessor');
		$gravatar = Mockery::mock('emberlabs\GravatarLib\Gravatar');
		$this->loader = new HelperLoader($preprocessor, $gravatar);
	}



	public function dataLoad()
	{
		return array(
			array('description'),
			array('licenses'),
			array('gravatar'),
			array('profile'),
		);
	}


	/**
	 * @dataProvider dataLoad
	 * @param string
	 */
	public function testLoad($helper)
	{
		$callback = callback($this->loader->__invoke($helper));
		Assert::true($callback->callable);
	}



	public function testInvalidLoad()
	{
		Assert::null($this->loader->__invoke('invalidHelper'));
	}
}

id(new HelperLoaderTest)->run();