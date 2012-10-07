<?php

namespace NetteAddons\Test;

use Mockery,
	PHPUnit_Framework_TestCase;



abstract class TestCase extends PHPUnit_Framework_TestCase
{
	protected function tearDown()
	{
		parent::tearDown();
		Mockery::close();
	}
}
