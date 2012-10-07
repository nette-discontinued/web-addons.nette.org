<?php

namespace NetteAddons\Test;

use NetteAddons\HelperLoader;
use Mockery;



/**
 * @author Patrik Votoček
 */
class HelperLoaderTest extends TestCase
{
	/** @var \NetteAddons\HelperLoader */
	private $loader;



	protected function setUp()
	{
		parent::setUp();
		$preprocessor = Mockery::mock('NetteAddons\TextPreprocessor');
		$this->loader = new HelperLoader($preprocessor);
	}



	public function dataLoad()
	{
		return array(
			array('description'),
			array('licenses'),
		);
	}


	/**
	 * @dataProvider dataLoad
	 * @param string
	 */
	public function testLoad($helper)
	{
		$callback = callback($this->loader->__invoke($helper));
		$this->assertTrue($callback->callable, "$helper is not callable");
	}



	public function testInvalidLoad()
	{
		$helper = 'invalidHelper';
		$this->assertNull($this->loader->__invoke($helper), "$helper is not null");
	}
}
