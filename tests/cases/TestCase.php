<?php

namespace NetteAddons\Test;

use PHPUnit_Framework_TestCase;
use Mockery;



abstract class TestCase extends PHPUnit_Framework_TestCase
{
	protected function tearDown()
	{
		parent::tearDown();
		Mockery::close();
	}
}
